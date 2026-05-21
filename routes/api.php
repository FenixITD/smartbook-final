<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', LoginController::class);

require __DIR__.'/api/authors.php';
require __DIR__.'/api/books.php';
require __DIR__.'/api/cartItems.php';
require __DIR__.'/api/favorites.php';
require __DIR__.'/api/genres.php';
require __DIR__.'/api/orderItems.php';
require __DIR__.'/api/orders.php';
require __DIR__.'/api/reviews.php';
