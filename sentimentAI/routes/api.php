<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostagemController;
use App\Http\Controllers\DashboardController;

Route::post('/register', [AuthController::class, 'apiRegister']); 
Route::post('/login', [AuthController::class, 'apiLogin']);       

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'apiLogout']);

    Route::apiResource('postagens', PostagemController::class);

    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getStats']);
        Route::get('/trends', [DashboardController::class, 'getTrends']);
        Route::get('/sentiment-distribution', [DashboardController::class, 'getSentimentDistribution']);
        Route::get('/social-media-stats', [DashboardController::class, 'getSocialMediaStats']);
    });
});