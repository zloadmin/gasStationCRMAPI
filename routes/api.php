<?php

use App\Http\Controllers\Api\FuelArrivalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::apiResource('fuel-arrivals', FuelArrivalController::class);
