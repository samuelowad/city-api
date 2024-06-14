<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CsrfTokenController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/cities', [CityController::class, 'index']);
Route::post('/cities', [CityController::class, 'store']);
Route::get('/cities/{id}', [CityController::class, 'show']);
Route::put('/cities/{id}', [CityController::class, 'update']);
Route::delete('/cities/{id}', [CityController::class, 'destroy']);

Route::get('/csrf-token', [CsrfTokenController::class, 'getToken']);
