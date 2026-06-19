<?php

use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/ui/orders/export', [DashboardController::class, 'exportOrdersCsv'])->name('ui.orders.export');

Route::post('/ui/users', [DashboardController::class, 'createUser'])->name('ui.users.store');
Route::post('/ui/users/{id}/update', [DashboardController::class, 'updateUser'])->name('ui.users.update');
Route::post('/ui/users/{id}/delete', [DashboardController::class, 'deleteUser'])->name('ui.users.delete');

Route::post('/ui/foods', [DashboardController::class, 'createFood'])->name('ui.foods.store');
Route::post('/ui/foods/{id}/update', [DashboardController::class, 'updateFood'])->name('ui.foods.update');
Route::post('/ui/foods/{id}/delete', [DashboardController::class, 'deleteFood'])->name('ui.foods.delete');

Route::post('/ui/orders', [DashboardController::class, 'createOrder'])->name('ui.orders.store');
Route::post('/ui/orders/{id}/update', [DashboardController::class, 'updateOrder'])->name('ui.orders.update');
Route::post('/ui/orders/{id}/delete', [DashboardController::class, 'deleteOrder'])->name('ui.orders.delete');
