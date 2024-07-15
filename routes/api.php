<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/send-email', [EmailController::class, 'sendEmail']);

Route::get('/retrieve-emails', [EmailController::class, 'retrieveEmail']);