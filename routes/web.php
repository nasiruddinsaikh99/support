<?php

use App\Http\Controllers\EmailController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

// Route::get('/api/send-email', [EmailController::class, 'sendEmail']);
Route::get('/api/retrieve-email', [EmailController::class, 'retrieveEmail']);

require __DIR__.'/auth.php';
