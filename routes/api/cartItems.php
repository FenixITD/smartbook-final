<?php

use App\Http\Controllers\Api\CartItems\CreateCartItemController;
use App\Http\Controllers\Api\CartItems\DeleteCartItemController;
use App\Http\Controllers\Api\CartItems\GetCartItemController;
use App\Http\Controllers\Api\CartItems\GetListCartItemController;
use App\Http\Controllers\Api\CartItems\UpdateCartItemController;
use Illuminate\Support\Facades\Route;

Route::prefix('cartItems')->name('api.cartItems.')->group(function (): void {
    Route::get('/', GetListCartItemController::class)->name('list');
    Route::get('/{cartItem}', GetCartItemController::class)->name('show');
    Route::post('/', CreateCartItemController::class)->name('create');
    Route::put('/{cartItem}', UpdateCartItemController::class)->name('update');
    Route::delete('/{cartItem}', DeleteCartItemController::class)->name('delete');
});
