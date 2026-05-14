<?php

use App\Http\Controllers\Api\Orders\CreateOrderController;
use App\Http\Controllers\Api\Orders\DeleteOrderController;
use App\Http\Controllers\Api\Orders\GetOrderController;
use App\Http\Controllers\Api\Orders\GetListOrderController;
use App\Http\Controllers\Api\Orders\SearchSuggestController;
use App\Http\Controllers\Api\Orders\UpdateOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/orders/suggest', SearchSuggestController::class)->name('api.orders.suggest');

Route::prefix('orders')->name('api.orders.')->group(function (): void {
    Route::get('/suggest', SearchSuggestController::class)->name('suggest');
    Route::get('/', GetListOrderController::class)->name('list');
    Route::get('/{order}', GetOrderController::class)->name('show');
    Route::post('/', CreateOrderController::class)->name('create');
    Route::put('/{order}', UpdateOrderController::class)->name('update');
    Route::delete('/{order}', DeleteOrderController::class)->name('delete');
});
