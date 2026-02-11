<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('transactions/stats', [TransactionController::class, 'stats']);
    Route::get('transactions/trashed', [TransactionController::class, 'trashed']);
    Route::post('transactions/{id}/restore', [TransactionController::class, 'restore']);
    Route::delete('transactions/{id}/force-delete', [TransactionController::class, 'forceDelete']);
    Route::apiResource('transactions', TransactionController::class);
});


