<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SalesTargetController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActivityTypeController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\KamPerformanceController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PrismApiController;
use App\Http\Controllers\DashboardController;


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
    Route::get('/', [ActivityController::class, 'index']);
    Route::post('/', [ActivityController::class, 'store']);
    Route::get('{task}', [ActivityController::class, 'show']);
    Route::put('{task}', [ActivityController::class, 'update']);
    Route::delete('{task}', [ActivityController::class, 'destroy']);
    Route::get('summary/{kamId}', [ActivityController::class, 'statusSummary']);
    Route::post('notes', [ActivityController::class, 'addNotes']);
    Route::post('update-status', [ActivityController::class, 'updateStatus']);
});

Route::prefix('activity-types')->group(function () {
    Route::get('/', [ActivityTypeController::class, 'index']);
    Route::post('/', [ActivityTypeController::class, 'store']);
    Route::get('{activityType}', [ActivityTypeController::class, 'show']);
    Route::put('{activityType}', [ActivityTypeController::class, 'update']);
    Route::delete('{activityType}', [ActivityTypeController::class, 'destroy']);
});

Route::prefix('dashboard')->group(function () {
    Route::get('admin/summary', [DashboardController::class, 'adminSummary']);
    Route::get('admin/kpi/summary', [DashboardController::class, 'kpiSummary']);

});





Route::prefix('kam-performance')->group(function () {
    Route::get('/', [KamPerformanceController::class, 'index']);
    Route::get('/kam-performance-breakdown', [KamPerformanceController::class, 'getTransferredPreviousMonthBreakdown']);
});



Route::prefix('clients')->group(function () {
    Route::get('/', [ClientController::class, 'index']);
});


Route::prefix('prism')->group(function () {
    Route::get('/branch-list', [PrismApiController::class, 'branchList']);
    Route::get('/branch-wise-supervisor-list/{branch_id}', [PrismApiController::class, 'branchWiseSupervisorList']);
    Route::get('/supervisor-wise-kam-list/{supervisor_id}', [PrismApiController::class, 'supervisorWiseKamList']);
    Route::get('/kam-wise-client-list/{kam_id}', [PrismApiController::class, 'kamWiseClientList']);
    Route::get('/kam-list', [PrismApiController::class, 'kamList']);
    Route::get('/client-list', [PrismApiController::class, 'clientList']);
    Route::get('/supervisor-list', [PrismApiController::class, 'supervisorList']);
});



