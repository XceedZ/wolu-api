<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Authentication routes
Route::post('/login', [UserController::class, 'login']);
Route::post('/signup', [UserController::class, 'signup']);
