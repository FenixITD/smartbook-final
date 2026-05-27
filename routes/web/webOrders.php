<?php

use App\Http\Controllers\Web\Orders\CreateOrderController;
use App\Http\Controllers\Web\Orders\DeleteOrderController;
use App\Http\Controllers\Web\Orders\GetByIdOrderController;
use App\Http\Controllers\Web\Orders\GetListOrderController;
use App\Http\Controllers\Web\Orders\UpdateOrderController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureUserIsAdmin::class])->prefix('orders')->name('orders.')->group(function (): void {
    Route::get('/', GetListOrderController::class)->name('index');
    Route::get('/create', [CreateOrderController::class, 'create'])->name('create');
    Route::post('/', [CreateOrderController::class, 'store'])->name('store');
    Route::get('/{order}', GetByIdOrderController::class)->name('show')->whereNumber('order');
    Route::get('/{order}/edit', [UpdateOrderController::class, 'edit'])->name('edit')->whereNumber('order');
    Route::put('/{order}', [UpdateOrderController::class, 'update'])->name('update')->whereNumber('order');
    Route::delete('/{order}', DeleteOrderController::class)->name('destroy')->whereNumber('order');
});
