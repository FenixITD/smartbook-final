<?php

use App\Http\Controllers\Web\CartItems\AddToCartWebController;
use App\Http\Controllers\Web\CartItems\RemoveFromCartWebController;
use App\Http\Controllers\Web\CartItems\ShowCartWebController;
use App\Http\Controllers\Web\CartItems\UpdateCartItemWebController;
use Illuminate\Support\Facades\Route;

Route::get('/cart', ShowCartWebController::class)->name('cart.index');
Route::post('/cart', AddToCartWebController::class)->name('cart.store');
Route::put('/cart/{bookId}', UpdateCartItemWebController::class)->name('cart.update');
Route::delete('/cart/{bookId}', RemoveFromCartWebController::class)->name('cart.destroy');
