<?php

use App\Http\Controllers\Api\FuelArrivalController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::apiResource('fuel-arrivals', FuelArrivalController::class);
Route::get('/transactions', [TransactionController::class, 'index']);
Route::get('/services', [ServiceController::class, 'index']);

