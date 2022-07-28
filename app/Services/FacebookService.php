<?php

namespace App\Services;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Illuminate\Support\ServiceProvider;
use Facebook\Facebook;

class FacebookService extends ServiceProvider
{
    protected $facebook;

    public function __construct()
    {
        $this->facebook = new Facebook([
            'app_id' => env('FACEBOOK_APP_ID', false),
            'app_secret' => env('FACEBOOK_SECRET', false),
            'grant_type' => "client_credentials",
            'default_graph_version' => 'v12.0'
        ]);
    }

    public function delete_post_by_id($post_id, $page_token, $user_token)
    {
        return $this->facebook->delete("/" . $post_id . "?access_token=" . $page_token);
    }

    public function update_post_by_id($post_id, $page_token, $message)
    {
        return $this->facebook->post("/" . $post_id . "?message=" . $message . "&access_token=" . $page_token);
    }

    public function redirectTo()
    {
        $helper = $this->facebook->getRedirectLoginHelper();

        $permissions = [
            'pages_manage_posts',
            'pages_read_engagement'
        ];

        return $helper->getLoginUrl("http://localhost:8000/auth/facebook/callback", $permissions);
    }

    public function handleCallback()
    {
        $helper = $this->facebook->getRedirectLoginHelper();

        if (request('state')) {
            $helper->getPersistentDataHandler()->set('state', request('state'));
        }

        try {
            $accessToken = $helper->getAccessToken();
        } catch (FacebookResponseException $e) {
        } catch (FacebookSDKException $e) {
        }

        if (!isset($accessToken)) {
        }

        if (!$accessToken->isLongLived()) {
            try {
                $oAuth2Client = $this->facebook->getOAuth2Client();
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (FacebookSDKException $e) {
            }
        };

        $this->facebook->setDefaultAccessToken($accessToken->getValue());
        $profileRequest = $this->facebook->get('/me?fields=name,first_name,last_name,email,link,gender,locale,cover,picture');
        $fbUserProfile = $profileRequest->getGraphNode()->asArray();

        $data = [
            "user_Fname" => $fbUserProfile['first_name'],
            "user_Lname" => $fbUserProfile['last_name'],
            "user_id" => $fbUserProfile['id'],
            "user_email" => (isset($fbUserProfile['email'])) ? $fbUserProfile['email'] : $fbUserProfile['first_name'] . "@" . $fbUserProfile['last_name'],
            "user_accessToken" => $accessToken->getValue(),
            "user_picture" => $fbUserProfile['picture']['url']
        ];

        return $data;
    }


    public function getPages($accessToken)
    {
        $pages = $this->facebook->get('/me/accounts', $accessToken);
        $pages = $pages->getGraphEdge()->asArray();

        return array_map(function ($item) {
            return [
                'access_token' => $item['access_token'],
                'id' => $item['id'],
                'name' => $item['name'],
                'image' => "https://graph.facebook.com/{$item['id']}/picture?type=large"
            ];
        }, $pages);
    }

    private function postData($accessToken, $endpoint, $data)
    {
        try {
            $response = $this->facebook->post($endpoint, $data, $accessToken);
            return $response->getGraphNode();

        } catch (FacebookResponseException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        } catch (FacebookSDKException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function post($accountId, $accessToken, $data, $images = [])
    {
        if ($images != []) {
            $attachments = $this->uploadImages($accountId, $accessToken, $images);
            foreach ($attachments as $i => $attachment) {
                $data["attached_media[$i]"] = "{\"media_fbid\":\"$attachment\"}";
            }
        }

        try {
            return $this->postData($accessToken, "$accountId/feed", $data);
        } catch (\Exception $e) {
        }
    }

    private function uploadImages($accountId, $accessToken, $images = [])
    {
        $attachments = [];
        $data = [
            'source' => $this->facebook->fileToUpload($images),
        ];

        try {
            $response = $this->postData($accessToken, "$accountId/photos?published=false", $data);
            if ($response) $attachments[] = $response['id'];
        } catch (\Exception $exception) {
            throw new Exception("Error while posting: {$exception->getMessage()}", $exception->getCode());
        }
        return $attachments;

        foreach ($images as $image) {
            if (!file_exists($image)) continue;
            $data = [
                'source' => $this->facebook->fileToUpload($image),
            ];

            try {
                $response = $this->postData($accessToken, "$accountId/photos?published=false", $data);
                if ($response) $attachments[] = $response['id'];
            } catch (\Exception $exception) {
                throw new Exception("Error while posting: {$exception->getMessage()}", $exception->getCode());
            }
        }

        return $attachments;
    }

    public function get_page_posts($accessToken, $pageId, $tokenPage)
    {
        $data = [];
        try {
            $response = $this->facebook->get('/' . $pageId . "/posts?fields=message,story,full_picture,is_published,scheduled_publish_time,created_time", $accessToken);
            $responseSchedule = $this->facebook->get('/' . $pageId . "/scheduled_posts?fields=message,story,full_picture,is_published,scheduled_publish_time,created_time", $tokenPage);

            $response = $response->getGraphEdge()->asArray();
            if (isset($responseSchedule))
                return array_merge($response, $responseSchedule->getGraphEdge()->asArray());

            return $response;

        } catch (FacebookResponseException $e) {
        } catch (FacebookSDKException $e) {
        }
    }
}
