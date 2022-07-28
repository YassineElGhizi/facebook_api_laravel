<?php

namespace App\Http\Controllers;

use App\Models\FacebookPage;
use App\Models\Post;
use App\Models\User;
use App\Services\FacebookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class FacebookController extends Controller
{
    protected $facebook;

    public function __construct()
    {
        $this->facebook = new FacebookService();
    }

    public function delete_post(Request $request)
    {
        $this->facebook->delete_post_by_id($request->page_id, $request->page_token);
        Session::flash('message', "Post Has Been delete successfully");
        return back();
    }

    public function update_post(Request $request)
    {
        Session::flash('message', "Post Has Been updated successfully");
        $this->facebook->update_post_by_id($request->page_id, $request->page_token, $request->new_message);
        return back();
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

        // if the user exist
        $credentials = ["email" => $user->email, "password" => $data['user_id']];
        if (Auth::attempt($credentials)) {
        } else {
            Auth::login($user, true);
        }

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
        $data = $this->facebook->get_page_posts($token, $id, $tokenPage);
        $collection = [];
        foreach ($data as $item) {
            $post = new Post();
            $post->created_time = isset($item["created_time"]) ? $item["created_time"] : null;
            $post->id_page = $item["id"];
            $post->message = isset($item["message"]) ? $item["message"] : null;
            $post->full_picture = isset($item['full_picture']) ? $item['full_picture'] : null;
            $post->scheduled_publish_time = isset($item['scheduled_publish_time']) ? date("Y-m-d H:i:s", $item['scheduled_publish_time']) : null;

            if ($post->message != null)
                $post->type = "message";
            else if ($post->full_picture != null)
                $post->type = "image";
            else
                $post->type = "video";

            array_push($collection, $post);
        }

        return View("post", ['posts' => $collection, "idpage" => $id, "tokenPage" => $tokenPage]);
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

        $this->facebook->post($accountId, $tokenPage, $data, $images);

        Session::flash('message', "Post Has Been created successfully");
        return back();
    }
}
