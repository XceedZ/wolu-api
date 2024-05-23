<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('auth/google', [UserController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [UserController::class, 'handleGoogleCallback']);


Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__.'/auth.php';
