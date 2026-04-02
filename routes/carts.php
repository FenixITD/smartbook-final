<?php

use App\Http\Controllers\Web\CartItems\AddToCartWebController;
use App\Http\Controllers\Web\CartItems\RemoveFromCartWebController;
use App\Http\Controllers\Web\CartItems\ShowCartWebController;
use App\Http\Controllers\Web\CartItems\UpdateCartItemWebController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function (): void {
    Route::get('/cart', ShowCartWebController::class)->name('cart.index');
    Route::post('/cart', AddToCartWebController::class)->name('cart.store');
    Route::put('/cart/{cartItem}', UpdateCartItemWebController::class)->name('cart.update');
    Route::delete('/cart/{cartItem}', RemoveFromCartWebController::class)->name('cart.destroy');
});
