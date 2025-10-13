<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\CountyController;
use App\Http\Controllers\UserController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('places', PlaceController::class)->except(['index', 'show']);
    Route::apiResource('counties', CountyController::class)->except(['index', 'show']);
});

Route::apiResource('places', PlaceController::class)->only(['index', 'show']);
Route::apiResource('counties', CountyController::class)->only(['index', 'show']);

Route::get('/counties/{county}/places/{place}', [PlaceController::class, 'showInCounty']);
Route::get('/counties/{county}/abc', [PlaceController::class, 'getInitialsByCounty']);
Route::get('/counties/{county}/abc/{letter}', [PlaceController::class, 'getPlacesByInitial']);


Route::post('/users/login', [UserController::class, 'login']);
Route::get('/users', [UserController::class, 'index'])->middleware('auth:sanctum');
