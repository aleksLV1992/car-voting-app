<?php

use App\Http\Controllers\CarController;
use Illuminate\Support\Facades\Route;

Route::get('/{any?}', [CarController::class, 'index'])->where('any', '.*');
