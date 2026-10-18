<?php

use App\Http\Controllers\Api\CliTimerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('timer')->group(function () {
        Route::get('/status', [CliTimerController::class, 'status']);
        Route::post('/start', [CliTimerController::class, 'start']);
        Route::post('/pause', [CliTimerController::class, 'pause']);
        Route::post('/resume', [CliTimerController::class, 'resume']);
        Route::post('/stop', [CliTimerController::class, 'stop']);
    });
});
