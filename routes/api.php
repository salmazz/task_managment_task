<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Task\TaskController;
use Illuminate\Support\Facades\Route;


// Guest routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tasks', TaskController::class);
    Route::post('/tasks/{task}/dependencies', [TaskController::class, 'addDependencies']);
});

