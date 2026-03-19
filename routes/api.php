<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::prefix('auth')->group(function () {
    // login route
    Route::post('/login', [AuthController::class, 'login']);

    // protected routes
    Route::middleware('auth:sanctum')->group(function() {
        // logout route
        Route::post('/logout', [AuthController::class, 'logout']);

        // authenticated user route
        Route::get('/me', [AuthController::class, 'me']);
    });
});
