<?php

use App\Http\Controllers\Orders\CreateOrderController;
use App\Http\Controllers\Orders\DeleteOrderController;
use App\Http\Controllers\Orders\GetByIdOrderController;
use App\Http\Controllers\Orders\GetListOrderController;
use App\Http\Controllers\Orders\UpdateOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('orders')->group(function (): void {
    Route::get('/', GetListOrderController::class)->name('orders.list');
    Route::get('/{order}', GetByIdOrderController::class)->name('orders.show');
    Route::post('/', CreateOrderController::class)->name('orders.create');
    Route::put('/{order}', UpdateOrderController::class)->name('orders.update');
    Route::delete('/{order}', DeleteOrderController::class)->name('orders.delete');
});
