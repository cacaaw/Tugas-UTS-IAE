<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Health check endpoint
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'service' => 'user-service']);
});

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout']);
Route::get('/me', [UserController::class, 'me']);
Route::get('/me/order-summary', [UserController::class, 'myOrderSummary']);
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::patch('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::get('/users/{id}/orders', [UserController::class, 'orders']);
Route::get('/users/{id}/order-summary', [UserController::class, 'orderSummary']);
Route::patch('/users/{id}/status', [UserController::class, 'updateStatus']);
