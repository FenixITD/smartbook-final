<?php

use App\Http\Controllers\Web\Orders\CreateOrderWebController;
use App\Http\Controllers\Web\Orders\DeleteOrderWebController;
use App\Http\Controllers\Web\Orders\GetOrderWebController;
use App\Http\Controllers\Web\Orders\GetListOrderWebController;
use App\Http\Controllers\Web\Orders\UpdateOrderWebController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('orders')->name('orders.')->group(function (): void {
    Route::get('/', GetListOrderWebController::class)->name('index');
    Route::get('/create', [CreateOrderWebController::class, 'create'])->name('create');
    Route::post('/', [CreateOrderWebController::class, 'store'])->name('store');
    Route::get('/{order}', GetOrderWebController::class)->name('show')->whereNumber('order');
    Route::get('/{order}/edit', [UpdateOrderWebController::class, 'edit'])->name('edit')->whereNumber('order');
    Route::put('/{order}', [UpdateOrderWebController::class, 'update'])->name('update')->whereNumber('order');
    Route::delete('/{order}', DeleteOrderWebController::class)->name('destroy')->whereNumber('order');
});
