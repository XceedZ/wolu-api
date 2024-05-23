<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ClassUsersController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Rute untuk UserController
Route::post('/signup', [UserController::class, 'signup']);
Route::post('/login', [UserController::class, 'login']);

// Rute untuk ClassController
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/classes', [ClassController::class, 'index']);
    Route::post('/classes', [ClassController::class, 'store']);
    Route::get('/classes/{id}', [ClassController::class, 'show']);
    Route::put('/classes/{id}', [ClassController::class, 'update']);
    Route::delete('/classes/{id}', [ClassController::class, 'destroy']);

    // Rute untuk TaskController
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);

    // Rute untuk ClassUsersController
    Route::post('/classes/{class_id}/users/{user_id}', [ClassUsersController::class, 'store']);
    Route::delete('/classes/{class_id}/users/{user_id}', [ClassUsersController::class, 'destroy']);
    Route::get('/classes/{user_id}/users', [ClassUsersController::class, 'index']);
    Route::get('/classes/{class_id}/members', [ClassUsersController::class, 'getUsersInClass']);

    // Sharelink Class
    Route::post('/classes/{classId}/share', [UserController::class, 'shareClass']);
    Route::post('/joinclasses/{shareToken}', [UserController::class, 'joinClass']);
    Route::get('/joinclasses/{shareToken}', [UserController::class, 'joinClass']);
});
