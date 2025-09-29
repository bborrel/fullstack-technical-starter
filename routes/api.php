<?php

use App\Http\Controllers\DonationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// @todo group routes belonging to DonationController
Route::controller(DonationController::class)->group(function () {
    Route::post('/donations', 'store')->middleware('auth:sanctum');
    Route::patch('/donations/{donation}', 'update')->middleware('auth:sanctum');
    Route::get('/donations/history/{user_id}', 'historyByUser')->middleware('auth:sanctum')->whereNumber('user_id');
});

