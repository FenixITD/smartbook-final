<?php

declare(strict_types=1);

use App\Http\Controllers\Web\Favorites\ShowFavoritesController;
use App\Http\Controllers\Web\Favorites\ToggleFavoriteController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('favorites')->name('favorites.')->group(function (): void {
    Route::get('/', ShowFavoritesController::class)->name('index');
    Route::post('/toggle', ToggleFavoriteController::class)->name('toggle');
});
