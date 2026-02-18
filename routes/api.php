<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BudgetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    
    // User
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Transactions
    Route::prefix('transactions')->group(function () {
        Route::get('/stats', [TransactionController::class, 'stats']);
        Route::get('/trashed', [TransactionController::class, 'trashed']);
        Route::post('/{id}/restore', [TransactionController::class, 'restore']);
        Route::delete('/{id}/force-delete', [TransactionController::class, 'forceDelete']);
    });
    Route::apiResource('transactions', TransactionController::class);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Budgets
    Route::apiResource('budgets', BudgetController::class);
});


