<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\FirebaseController;

Route::get('/firebase-test', [FirebaseController::class, 'testConnection']);




use App\Http\Controllers\ScrapingController;

Route::get('/weather-map', [ScrapingController::class, 'showMap']);
