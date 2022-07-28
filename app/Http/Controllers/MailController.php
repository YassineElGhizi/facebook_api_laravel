<?php

namespace App\Http\Controllers;

use App\Mail\weeklyEmail;
use App\Services\FacebookService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;


class MailController extends Controller
{

    protected $facebook;

    public function __construct()
    {
        $this->facebook = new FacebookService();
    }

    public function send($id, $tokenPage)
    {
        $token = Auth::user()->token;
        $data = $this->facebook->getPostByPageId($token, $id, $tokenPage);

        $cnt = 0;
        foreach ($data as $item) {
            $carbonated_date_time = $item['created_time'];
            if ($carbonated_date_time > Carbon::now()->startOfWeek() && $carbonated_date_time < Carbon::now()->endOfWeek()) {
                $cnt += 1;
            }
        }

        $user = Auth::user();
        $mail = $user->email;
        $mail = str_replace("@Gh'z", "@gmail.com", $mail);
        Mail::to($mail)->send(new weeklyEmail(env('APP_NAME', false), $cnt));

        Session::flash('message', "A mail has been send successful");

        return back();
    }
}
