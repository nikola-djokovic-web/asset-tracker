<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\AssetAssignmentController;

// Javne rute (Public)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Zaštićene rute (Protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('assets', AssetController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('organizations', OrganizationController::class);
    Route::apiResource('users', UserController::class);

    Route::get('/tenant', [TenantController::class, 'show']);
    Route::put('/tenant', [TenantController::class, 'update']);

    Route::post('/assets/{asset}/checkout', [AssetAssignmentController::class, 'checkout']);
    Route::post('/assets/{asset}/checkin', [AssetAssignmentController::class, 'checkin']);
});
