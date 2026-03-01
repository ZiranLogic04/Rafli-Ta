<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\AdminLetterController;
use App\Http\Controllers\LetterTypeController;
use App\Http\Controllers\AdminUserController;

// --------------------------------------------------------------------------
// Diagnostic Routes
// --------------------------------------------------------------------------
Route::get('/debug-session-set', function (Request $request) {
    $request->session()->put('test_key', 'it_works');
    return ['status' => 'Session value set.', 'session_id' => $request->session()->getId()];
});
Route::get('/debug-session-get', function (Request $request) {
    return [
        'has_key' => $request->session()->has('test_key'),
        'value' => $request->session()->get('test_key', 'NOT_FOUND'),
        'session_id' => $request->session()->getId(),
    ];
});

// Removed all API routes, moving them to routes/api.php as per Sanctum standard

// --------------------------------------------------------------------------
// Vue SPA Catch-all Route
// --------------------------------------------------------------------------
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
