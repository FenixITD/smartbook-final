<?php

use App\Http\Controllers\Api\OrderItems\CreateOrderItemController;
use App\Http\Controllers\Api\OrderItems\DeleteOrderItemController;
use App\Http\Controllers\Api\OrderItems\GetOrderItemController;
use App\Http\Controllers\Api\OrderItems\GetListOrderItemController;
use App\Http\Controllers\Api\OrderItems\UpdateOrderItemController;
use Illuminate\Support\Facades\Route;

Route::prefix('orderItems')->name('api.orderItems.')->group(function (): void {
    Route::get('/', GetListOrderItemController::class)->name('list');
    Route::get('/{orderItem}', GetOrderItemController::class)->name('show');
    Route::post('/', CreateOrderItemController::class)->name('create');
    Route::put('/{orderItem}', UpdateOrderItemController::class)->name('update');
    Route::delete('/{orderItem}', DeleteOrderItemController::class)->name('delete');
});
