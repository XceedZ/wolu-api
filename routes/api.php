<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ClassUsersController;
use App\Http\Controllers\ForumController;
use App\Http\Middleware\CheckTokenExpiry;
use App\Http\Controllers\TaskSubmissionController;



Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Rute untuk UserController
Route::post('/signup', [UserController::class, 'signup']);
Route::post('/login', [UserController::class, 'login']);

// Rute untuk ClassController
Route::middleware(['auth:sanctum', CheckTokenExpiry::class])->group(function () {    
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
    Route::get('/tasks/class/{classId}', [TaskController::class, 'getTasksByClassId']);
    Route::post('/upload', [TaskController::class, 'upload']);

    // Rute untuk ClassUsersController
    Route::post('/classes/{class_id}/users/{user_id}', [ClassUsersController::class, 'store']);
    Route::delete('/classes/{class_id}/users/{user_id}', [ClassUsersController::class, 'destroy']);
    Route::get('/classes/{user_id}/users', [ClassUsersController::class, 'index']);
    Route::get('/classes/{class_id}/members', [ClassUsersController::class, 'getUsersInClass']);

    // Sharelink Class
    Route::post('/classes/{classId}/share', [UserController::class, 'shareClass']);
    Route::post('/joinclasses/{shareToken}', [UserController::class, 'joinClass']);
    Route::get('/joinclasses/{shareToken}', [UserController::class, 'joinClass']);

    // Forum
    Route::post('/chats', [ForumController::class, 'sendMessage']);
    Route::get('/chats/class/{classId}', [ForumController::class, 'getMessagesByClass']);

    // Ngumpulin tugas taskId, $studentId
    Route::post('/submissions', [TaskSubmissionController::class, 'store']);
    Route::get('/tasks/{taskId}/submissions', [TaskSubmissionController::class, 'getSubmissionsByTaskId']);
    Route::get('/tasks/{taskId}/students/{studentId}/submission', [TaskSubmissionController::class, 'getSubmissionByStudent']);
    Route::get('/tasks/{taskId}/students/{studentId}', [TaskSubmissionController::class, 'getSubmission']);
    Route::post('/submissions/{submissionId}/grade', [TaskSubmissionController::class, 'gradeSubmission']);
    Route::get('/submissions/{taskId}/classroom/{classroomId}/not-submitted', [TaskSubmissionController::class, 'getStudentsWhoHaveNotSubmitted']);

    // Quiz
    Route::get('quizzes', [QuizController::class, 'index']);
    Route::get('quizzes/{classid}', [QuizController::class, 'show']);
    Route::post('quizzes', [QuizController::class, 'store']);
    Route::put('quizzes/{id}', [QuizController::class, 'update']);
    Route::delete('quizzes/{id}', [QuizController::class, 'destroy']);
    
    Route::post('quizzes/{quizId}/results', [QuizController::class, 'storeResult']);
    Route::get('quizzes/{quizId}/results/{studentId}', [QuizController::class, 'getResult']);
    Route::get('classes/{classId}/quizzes', [QuizController::class, 'getQuizzesByClass']);

    Route::get('quizzes/{quizId}', [QuizController::class, 'getQuizForStudent']);


});
