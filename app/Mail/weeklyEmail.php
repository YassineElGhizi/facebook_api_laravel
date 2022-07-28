<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class weeklyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $app_name;
    public $number;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($app_name, $number)
    {
        $this->app_name = $app_name;
        $this->number = $number;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('weeklyMails');
    }
}
