<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\AdminLetterController;
use App\Http\Controllers\LetterTypeController;
use App\Http\Controllers\AdminUserController;

// Public Auth routes
Route::post('/login', [AuthController::class, 'apiLogin']);

// Protected SPA Auth routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'apiLogout']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'apiIndex']);

    // User letters
    Route::get('/letters', [LetterController::class, 'apiIndex']);
    Route::post('/letters', [LetterController::class, 'apiStore']);
    Route::get('/letters/create-data', [LetterController::class, 'apiCreateData']);
    Route::get('/letters/template/{id}', [LetterController::class, 'downloadTemplate']);
    Route::get('/letters/{id}/download', [LetterController::class, 'download']);

    // Admin-only endpoints
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Letters
        Route::get('/letters', [AdminLetterController::class, 'apiIndex']);
        Route::get('/letters/{id}', [AdminLetterController::class, 'apiShow']);
        Route::post('/letters/{id}/approve', [AdminLetterController::class, 'approve']);
        Route::post('/letters/{id}/reject', [AdminLetterController::class, 'reject']);

        // Types
        Route::get('/types', [LetterTypeController::class, 'apiIndex']);
        Route::post('/types', [LetterTypeController::class, 'store']);
        Route::put('/types/{id}', [LetterTypeController::class, 'update']);
        Route::delete('/types/{id}', [LetterTypeController::class, 'destroy']);
        Route::get('/types/{id}/download', [LetterTypeController::class, 'download']);

        // Users
        Route::get('/users', [AdminUserController::class, 'apiIndex']);
        Route::post('/users', [AdminUserController::class, 'store']);
        Route::put('/users/{id}', [AdminUserController::class, 'update']);
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
    });
});
