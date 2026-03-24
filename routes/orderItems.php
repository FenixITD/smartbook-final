<?php

use App\Http\Controllers\OrderItems\CreateOrderItemController;
use App\Http\Controllers\OrderItems\DeleteOrderItemController;
use App\Http\Controllers\OrderItems\GetByIdOrderItemController;
use App\Http\Controllers\OrderItems\GetListOrderItemController;
use App\Http\Controllers\OrderItems\UpdateOrderItemController;
use Illuminate\Support\Facades\Route;

Route::prefix('orderItems')->group(function (): void {
    Route::get('/', GetListOrderItemController::class)->name('orderItems.list');
    Route::get('/{orderItem}', GetByIdOrderItemController::class)->name('orderItems.show');
    Route::post('/', CreateOrderItemController::class)->name('orderItems.create');
    Route::put('/{orderItem}', UpdateOrderItemController::class)->name('orderItems.update');
    Route::delete('/{orderItem}', DeleteOrderItemController::class)->name('orderItems.delete');
});
