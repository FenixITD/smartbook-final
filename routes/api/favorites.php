<?php

use App\Http\Controllers\Favorites\CreateFavoriteController;
use App\Http\Controllers\Favorites\DeleteFavoriteController;
use App\Http\Controllers\Favorites\GetByIdFavoriteController;
use App\Http\Controllers\Favorites\GetListFavoriteController;
use App\Http\Controllers\Favorites\UpdateFavoriteController;
use Illuminate\Support\Facades\Route;

Route::prefix('favorites')->group(function (): void {
    Route::get('/', GetListFavoriteController::class)->name('favorites.list');
    Route::get('/{favorite}', GetByIdFavoriteController::class)->name('favorites.show');
    Route::post('/', CreateFavoriteController::class)->name('favorites.create');
    Route::put('/{favorite}', UpdateFavoriteController::class)->name('favorites.update');
    Route::delete('/{favorite}', DeleteFavoriteController::class)->name('favorites.delete');
});
