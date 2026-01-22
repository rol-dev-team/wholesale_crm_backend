<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SalesTargetController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\KamPerformanceController;
use App\Http\Controllers\ClientController;


// PUBLIC routes
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Example: protected user info
    Route::get('/user', function () {
        return auth()->user();
    });
});



Route::prefix('users')->group(function () {

    Route::get('/', [UserController::class, 'index']);      // List with pagination
    Route::post('/', [UserController::class, 'store']);     // Create

    Route::get('{id}', [UserController::class, 'show']);    // Single
    Route::put('{id}', [UserController::class, 'update']);  // Update
    Route::delete('{id}', [UserController::class, 'destroy']); // Delete

});




Route::prefix('sales-targets')->group(function () {
    Route::get('/', [SalesTargetController::class, 'index']);
    Route::post('/', [SalesTargetController::class, 'store']);
    Route::get('{id}', [SalesTargetController::class, 'show']);
    Route::put('{id}', [SalesTargetController::class, 'update']);
    Route::delete('{id}', [SalesTargetController::class, 'destroy']);
});


Route::prefix('tasks')->group(function () {
    Route::get('/', [TaskController::class, 'index']);
    Route::post('/', [TaskController::class, 'store']);
    Route::get('{task}', [TaskController::class, 'show']);
    Route::put('{task}', [TaskController::class, 'update']);
    Route::delete('{task}', [TaskController::class, 'destroy']);
});




Route::prefix('kam-performance')->group(function () {
    Route::get('/', [KamPerformanceController::class, 'index']);
});



Route::prefix('clients')->group(function () {
    Route::get('/', [ClientController::class, 'index']);
});

