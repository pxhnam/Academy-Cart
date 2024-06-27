<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Mail;


class MailHelpers
{
    public static function send($view, $params, $subject, $to, $name)
    {
        Mail::send($view, $params, function ($email) use ($subject, $to, $name) {
            $email->subject($subject);
            $email->to($to, $name);
        });
    }
}
