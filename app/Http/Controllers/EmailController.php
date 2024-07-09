<?php

namespace App\Http\Controllers;

use App\Mail\SupportEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    Public function sendEmail(){
        $toEmail = "developervikashkr@gmail.com";
        $message = "We are thrilled to have you on board. Our team is here to assist you with any questions or support you may need.";
        $subject = "Welcome to SupportCRM!";

        $request = Mail::to($toEmail)->send(new SupportEmail($message, $subject));

        dd($request);
    }
}
