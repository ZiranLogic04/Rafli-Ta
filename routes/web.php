<?php

use Illuminate\Support\Facades\Route;

// --------------------------------------------------------------------------
// Vue SPA Catch-all Route
// --------------------------------------------------------------------------
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
