<?php

use App\Http\Controllers\Api\CarApiController;
use App\Http\Controllers\Api\ModelController;
use App\Http\Controllers\Api\VotingController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::get('/voting/pair', [VotingController::class, 'getPair']);
    Route::post('/voting/{carId}', [VotingController::class, 'vote']);

    Route::get('/cars', [CarApiController::class, 'index']);
    Route::get('/cars/statistics', [CarApiController::class, 'statistics']);
    Route::get('/cars/models', [CarApiController::class, 'models']);

    Route::get('/models', [ModelController::class, 'index']);
    Route::get('/models/{make}', [ModelController::class, 'byMake']);
});
