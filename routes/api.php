<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\CountyController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('places', PlaceController::class)->except(['index', 'show']);
    Route::apiResource('counties', CountyController::class)->except(['index', 'show']);
});

Route::apiResource('places', PlaceController::class)->only(['index', 'show']);
Route::apiResource('counties', CountyController::class)->only(['index', 'show']);
