<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContributionController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/otp/send', [AuthController::class, 'sendOtp']);
    Route::post('/otp/verify', [AuthController::class, 'verifyOtp']);
});

Route::middleware('auth:sanctum')->prefix('v1/auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

Route::middleware('auth:sanctum')->prefix('v1')->group(function (): void {
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/next', [TaskController::class, 'next']);
    Route::post('/tasks/{id}/annotate', [TaskController::class, 'annotate']);
    Route::post('/tasks/{id}/skip', [TaskController::class, 'skip']);
    Route::get('/tasks/history', [TaskController::class, 'history']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);

    Route::get('/wallet/balance', [WalletController::class, 'balance']);
    Route::get('/wallet/transactions', [WalletController::class, 'transactions']);
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw']);

    Route::post('/sync/push', [SyncController::class, 'push']);
    Route::get('/sync/pull', [SyncController::class, 'pull']);

    Route::post('/contributions', [ContributionController::class, 'submit']);
    Route::get('/contributions/mine', [ContributionController::class, 'myContributions']);
    Route::get('/contributions/review/next', [ContributionController::class, 'nextReview']);
    Route::post('/contributions/{id}/review', [ContributionController::class, 'review']);
    Route::get('/contributions/{id}', [ContributionController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('v1/admin')->group(function (): void {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/users', [AdminController::class, 'users']);
    Route::patch('/users/{id}', [AdminController::class, 'updateUserStatus']);
    Route::get('/alerts', [AdminController::class, 'alerts']);
    Route::post('/tasks/upload', [AdminController::class, 'tasksUpload']);
    Route::get('/datasets', [AdminController::class, 'datasets']);
    Route::get('/datasets/{id}/export', [AdminController::class, 'exportDataset']);
    Route::patch('/config', [AdminController::class, 'updateConfig']);
});
