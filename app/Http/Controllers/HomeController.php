<?php

namespace App\Http\Controllers;

use App\Models\FacebookPage;
use App\Services\FacebookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->facebook = new FacebookService();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
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
        //var_dump($pages);
        return view('home', compact('pages'));
//        return view('home');
    }
}
