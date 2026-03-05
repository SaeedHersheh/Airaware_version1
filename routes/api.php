<?php

use App\Http\Controllers\ScrapingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::get('/show-map', [ScrapingController::class, 'showMapApi']);
