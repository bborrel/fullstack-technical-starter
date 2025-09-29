<?php

use App\Http\Controllers\DonationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// @todo group routes belonging to DonationController
Route::post('/donations', [DonationController::class, 'store'])
    ->middleware('auth:sanctum');
Route::patch('/donations/{donation}', [DonationController::class, 'update'])
    ->middleware('auth:sanctum');
Route::get('/donations/history/{user_id}', [DonationController::class, 'historyByUser'])
    ->middleware('auth:sanctum')
    ->whereNumber('user_id');

