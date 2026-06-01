<?php

declare(strict_types=1);

use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'))->name('home');
Route::get('dashboard', DashboardController::class)->name('dashboard');

require __DIR__.'/web/auth.php';
require __DIR__.'/web/catalog.php';
require __DIR__.'/web/carts.php';
require __DIR__.'/web/webFavorites.php';
require __DIR__.'/web/chat.php';
require __DIR__.'/web/search.php';
require __DIR__.'/web/webActivityLogs.php';
require __DIR__.'/web/webUserActivity.php';
require __DIR__.'/web/webAuthors.php';
require __DIR__.'/web/webBooks.php';
require __DIR__.'/web/webGenres.php';
require __DIR__.'/web/webOrders.php';
require __DIR__.'/web/webReviews.php';

require __DIR__.'/channels.php';
require __DIR__.'/settings.php';
