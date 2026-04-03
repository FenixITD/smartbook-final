<?php

use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'))->name('home');

Route::get('dashboard', DashboardController::class)->name('dashboard');

require __DIR__.'/authors.php';
require __DIR__.'/books.php';
require __DIR__.'/cartItems.php';
require __DIR__.'/favorites.php';
require __DIR__.'/genres.php';
require __DIR__.'/orders.php';
require __DIR__.'/orderItems.php';
require __DIR__.'/reviews.php';
require __DIR__.'/carts.php';
require __DIR__.'/webBooks.php';

require __DIR__.'/settings.php';
