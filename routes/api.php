<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SubcontractorController;
use App\Http\Middleware\SetRLSContext;

// auth endpoints
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

Route::middleware('auth:sanctum')->middleware(SetRLSContext::class)->group(function () {
    // subcontractor endpoints
    Route::prefix('subcontractors')->group(function () {
        Route::get('/', [SubcontractorController::class, 'index']);
        Route::post('/', [SubcontractorController::class, 'store']);
        Route::get('/{id}', [SubcontractorController::class, 'show']);
        Route::delete('/{id}', [SubcontractorController::class, 'destroy']);
        Route::get('/{subcontractorId}/documents', [DocumentController::class, 'index']);
    });

    // document endpoints
    Route::prefix('documents')->group(function () {
        Route::post('/', [DocumentController::class, 'store']);
        Route::get('/{id}', [DocumentController::class, 'show']);
        Route::patch('/{id}/validate', [DocumentController::class, 'confirm']);
        Route::patch('/{id}/reject', [DocumentController::class, 'reject']);
    });
});
