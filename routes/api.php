<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\OrderController as ControllersOrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PermissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});



// Public Routes (For development)

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('staff', StaffController::class);

    Route::get('/roles/permissions', [PermissionController::class, 'index']);
    Route::put('/roles/permissions', [PermissionController::class, 'update']);

    Route::apiResource('shop', ShopController::class);
    Route::apiResource('orders', OrderController::class);

    Route::get('/settings', [SettingController::class, 'show']);
    Route::put('/settings', [SettingController::class, 'update']);
    
    Route::get('/dashboard-stats', [ReportController::class, 'dashboardStats']);
    
    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/sales-summary', [ReportController::class, 'salesSummary']);
        Route::get('/best-selling', [ReportController::class, 'bestSellingProducts']);
    });
});

