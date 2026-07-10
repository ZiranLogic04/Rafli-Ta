<?php

use App\Http\Controllers\AdminDepartmentController;
use App\Http\Controllers\AdminLetterController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\LetterTypeController;
use App\Http\Controllers\ProdiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Department endpoint
Route::get('/public/departments', [AdminDepartmentController::class, 'apiIndex']);

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
    Route::get('/letters/{id}/download', [LetterController::class, 'download']);
    Route::patch('/letters/{letter}/number', [LetterController::class, 'updateLetterNumber']);
    Route::delete('/letters/{id}', [LetterController::class, 'apiDestroy']);

    // All letters / Inbox
    Route::get('/approvals', [AdminLetterController::class, 'apiInbox']);

    // Admin-only endpoints
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Letters
        Route::get('/letters', [AdminLetterController::class, 'apiIndex']);
        Route::get('/letters/{id}', [AdminLetterController::class, 'apiShow']);
        Route::patch('/letters/{id}/letter-number', [AdminLetterController::class, 'updateLetterNumber']);

        // Types
        Route::get('/types', [LetterTypeController::class, 'apiIndex']);
        Route::post('/types', [LetterTypeController::class, 'store']);
        Route::put('/types/{id}', [LetterTypeController::class, 'update']);
        Route::delete('/types/{id}', [LetterTypeController::class, 'destroy']);

        // Users
        Route::get('/users', [AdminUserController::class, 'apiIndex']);
        Route::post('/users', [AdminUserController::class, 'store']);
        Route::put('/users/{id}', [AdminUserController::class, 'update']);
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);

        // Departments
        Route::get('/departments', [AdminDepartmentController::class, 'apiIndex']);
        Route::post('/departments', [AdminDepartmentController::class, 'store']);
        Route::put('/departments/{id}', [AdminDepartmentController::class, 'update']);
        Route::delete('/departments/{id}', [AdminDepartmentController::class, 'destroy']);

        // Prodis
        Route::get('/prodis', [ProdiController::class, 'index']);
        Route::post('/prodis', [ProdiController::class, 'store']);
        Route::put('/prodis/{id}', [ProdiController::class, 'update']);
        Route::delete('/prodis/{id}', [ProdiController::class, 'destroy']);

        // Permissions
        Route::get('/permissions/{role}', [LetterTypeController::class, 'apiRolePermissions']);
        Route::post('/permissions/{role}', [LetterTypeController::class, 'apiSaveRolePermissions']);
    });
});
