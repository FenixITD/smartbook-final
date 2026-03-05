<?php

use App\Http\Controllers\CartItems\CreateCartItemController;
use App\Http\Controllers\CartItems\DeleteCartItemController;
use App\Http\Controllers\CartItems\GetByIdCartItemController;
use App\Http\Controllers\CartItems\GetListCartItemController;
use App\Http\Controllers\CartItems\UpdateCartItemController;
use Illuminate\Support\Facades\Route;

Route::prefix('cartItems')->group(function (): void {
    Route::get('/', GetListCartItemController::class)->name('cartItems.list');
    Route::get('/{cartItem}', GetByIdCartItemController::class)->name('cartItems.show');
    Route::post('/', CreateCartItemController::class)->name('cartItems.create');
    Route::put('/{cartItem}', UpdateCartItemController::class)->name('cartItems.update');
    Route::delete('/{cartItem}', DeleteCartItemController::class)->name('cartItems.delete');
});
