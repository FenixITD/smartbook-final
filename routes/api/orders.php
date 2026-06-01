<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Orders\CreateOrderController;
use App\Http\Controllers\Api\Orders\DeleteOrderController;
use App\Http\Controllers\Api\Orders\GetOrderController;
use App\Http\Controllers\Api\Orders\GetListOrderController;
use App\Http\Controllers\Api\Orders\UpdateOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('orders')->name('api.orders.')->group(function (): void {
    Route::get('/', GetListOrderController::class)->name('list');
    Route::get('/{order}', GetOrderController::class)->name('show');
    Route::post('/', CreateOrderController::class)->name('create');
    Route::put('/{order}', UpdateOrderController::class)->name('update');
    Route::delete('/{order}', DeleteOrderController::class)->name('delete');
});
