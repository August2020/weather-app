<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\WeatherController;

Route::get('/clients', [ClientController::class, 'index']);
Route::post('/clients', [ClientController::class, 'store']);
Route::get('/weather', [WeatherController::class, 'getWeather']);