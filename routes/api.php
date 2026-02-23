<?php

use App\Http\Controllers\Api\V1\{
    AuthController,
    DashboardController,
    TransactionController,    
    CategoryController,
    BudgetController
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        
        // Users
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // Dashboard
        Route::get('/dashboard/stats', [DashboardController::class, 'index']);

        // Transactions
        Route::prefix('transactions')->group(function () {
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

});

