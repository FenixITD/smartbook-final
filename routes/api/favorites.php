<?php

use App\Http\Controllers\Api\Favorites\CreateFavoriteController;
use App\Http\Controllers\Api\Favorites\DeleteFavoriteController;
use App\Http\Controllers\Api\Favorites\GetFavoriteController;
use App\Http\Controllers\Api\Favorites\GetListFavoriteController;
use App\Http\Controllers\Api\Favorites\UpdateFavoriteController;
use Illuminate\Support\Facades\Route;

Route::prefix('favorites')->name('api.favorites.')->group(function (): void {
    Route::get('/', GetListFavoriteController::class)->name('list');
    Route::get('/{favorite}', GetFavoriteController::class)->name('show')->whereNumber('favorite');
    Route::post('/', CreateFavoriteController::class)->name('create');
    Route::put('/{favorite}', UpdateFavoriteController::class)->name('update')->whereNumber('favorite');
    Route::delete('/{favorite}', DeleteFavoriteController::class)->name('delete')->whereNumber('favorite');
});
