<?php

namespace App\Http\Controllers;

use App\Models\FacebookPage;
use App\Models\Post;
use App\Models\User;
use App\Page;
use App\Services\FacebookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class FacebookController extends Controller
{

    protected $facebook;

    public function __construct()
    {
        $this->facebook = new FacebookService();
    }

    public function facebook_provider()
    {
        return redirect($this->facebook->redirectTo());
    }

    public function handle_callback()
    {
        $data = $this->facebook->handleCallback();

        $user = new User();
        $user->name = $data['user_Fname'] . " " . $data['user_Lname'];
        $user->email = $data['user_email'];
        $user->password = Hash::make($data['user_id']);
        $user->token = $data['user_accessToken'];
        $user->facebook_app_id = $data['user_id'];
        $user->picture = $data['user_picture'];


        // if user exist set auth else created (remember user) save data in database
        $credentials = ["email" => $user->email, "password" => $data['user_id']];

        if (Auth::attempt($credentials)) {
        } else {
            Auth::login($user, true);
        }
        $this->goToHomePage();

    }

    public function goToHomePage()
    {
        $pages = $this->facebook->getPages(Auth::user()->token);

        $collection = [];
        foreach ($pages as $item) {
            $page = new FacebookPage();
            $page->id = $item["id"];
            $page->access_token = $item["access_token"];
            $page->name = $item["name"];
            $page->image = $item["image"];

            array_push($collection, $page);

        }
        $pages = $collection;
        return view('home', compact('pages'));
    }

    public function get_post($id, $tokenPage)
    {
        $token = Auth::user()->token;
        $data = $this->facebook->getPostByPageId($token, $id, $tokenPage);

        $collection = [];
        foreach ($data as $item) {
            $post = new Post();
            $post->created_time = isset($item["created_time"]) ? $item["created_time"] : null;
            $post->id_page = $item["id"];
            $post->message = isset($item["message"]) ? $item["message"] : null;
            $post->story = isset($item['story']) ? $item['story'] : null;
            $post->full_picture = isset($item['full_picture']) ? $item['full_picture'] : null;
            $post->is_published = isset($item['is_published']) ? $item['is_published'] : null;
            $post->scheduled_publish_time = isset($item['scheduled_publish_time']) ? $item['scheduled_publish_time'] : null;

            if ($post->message != null)
                $post->type = "message";
            else if ($post->story != null)
                $post->type = "story";
            else if ($post->full_picture != null)
                $post->type = "image";
            else
                $post->type = "video";

            array_push($collection, $post);
        }

        return View("post", ['posts' => $collection, "idpage" => $id, "tokenPage" => $tokenPage]);
    }


    public function getPostByPageId($accessToken, $pageId, $tokenPage)
    {

        $data = [];
        // here i send to request for get post published and  scheduled_posts and i merge all data
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


    public function create_post(Request $request)
    {


        $isSchedule = $request->inlineCheckbox1;
        $description = $request->description;
        $date = $request->dateSchedule;
        $tokenPage = $request->tokenPage;
        $accountId = $request->idpage;


        $images = (isset($request->fileUpload)) ? $request->fileUpload : [];

        $data = [];
        if ($isSchedule)
            $data = ['message' => $description,
                'published' => false,
                "scheduled_publish_time" => $date];
        else
            $data = ['message' => $description];


        $content = $data;
        $this->facebook->post($accountId, $tokenPage, $content, $images);

        return back();
    }

    public function delete_post(Request $request)
    {
        $this->facebook->delete_post_by_id($request->page_id, $request->page_token, Auth::user()->token);
        return back();
    }

}
