<?php

use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'))->name('home');

Route::get('dashboard', DashboardController::class)->name('dashboard');

require __DIR__.'/api/authors.php';
require __DIR__.'/api/books.php';
require __DIR__.'/api/cartItems.php';
require __DIR__.'/api/favorites.php';
require __DIR__.'/api/genres.php';
require __DIR__.'/api/orders.php';
require __DIR__.'/api/orderItems.php';
require __DIR__.'/api/reviews.php';
require __DIR__.'/web/webAuthors.php';
require __DIR__.'/web/webBooks.php';
require __DIR__.'/web/webGenres.php';
require __DIR__.'/web/webOrders.php';
require __DIR__.'/web/webOrders.php';
require __DIR__.'/web/webReviews.php';
require __DIR__.'/web/carts.php';

require __DIR__.'/settings.php';
