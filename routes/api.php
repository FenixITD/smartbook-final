<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Middleware\EnsureUserIsAdmin;

Route::post('/auth/login', LoginController::class);

Route::middleware(['auth:sanctum', EnsureUserIsAdmin::class])->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    require __DIR__.'/api/authors.php';
    require __DIR__.'/api/books.php';
    require __DIR__.'/api/cartItems.php';
    require __DIR__.'/api/favorites.php';
    require __DIR__.'/api/genres.php';
    require __DIR__.'/api/orderItems.php';
    require __DIR__.'/api/orders.php';
    require __DIR__.'/api/reviews.php';
});
