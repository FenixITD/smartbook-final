<?php

declare(strict_types=1);

use App\Http\Controllers\Web\CartItems\AddToCartController;
use App\Http\Controllers\Web\CartItems\ClearCartController;
use App\Http\Controllers\Web\CartItems\RemoveFromCartController;
use App\Http\Controllers\Web\CartItems\ShowCartController;
use App\Http\Controllers\Web\CartItems\UpdateCartItemController;
use Illuminate\Support\Facades\Route;

Route::get('/cart', ShowCartController::class)->name('cart.index');
Route::post('/cart', AddToCartController::class)->name('cart.store');
Route::put('/cart/{bookId}', UpdateCartItemController::class)->name('cart.update')->whereNumber('bookId');
Route::delete('/cart/{bookId}', RemoveFromCartController::class)->name('cart.destroy')->whereNumber('bookId');
Route::delete('/cart', ClearCartController::class)->name('cart.clear');
