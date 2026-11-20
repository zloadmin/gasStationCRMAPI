<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FuelArrivalController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\ProfitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;





Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('fuel-arrivals', FuelArrivalController::class);
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/payment-types', [TransactionController::class, 'paymentTypes']);
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/profit', [ProfitController::class, 'index']);
    Route::get('/user', [AuthController::class, 'user']);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
