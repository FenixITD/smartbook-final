<?php

use App\Http\Controllers\Api\Favorites\CreateFavoriteController;
use App\Http\Controllers\Api\Favorites\DeleteFavoriteController;
use App\Http\Controllers\Api\Favorites\GetByIdFavoriteController;
use App\Http\Controllers\Api\Favorites\GetListFavoriteController;
use App\Http\Controllers\Api\Favorites\UpdateFavoriteController;
use Illuminate\Support\Facades\Route;

Route::prefix('favorites')->name('api.favorites.')->group(function (): void {
    Route::get('/', GetListFavoriteController::class)->name('list');
    Route::get('/{favorite}', GetByIdFavoriteController::class)->name('show');
    Route::post('/', CreateFavoriteController::class)->name('create');
    Route::put('/{favorite}', UpdateFavoriteController::class)->name('update');
    Route::delete('/{favorite}', DeleteFavoriteController::class)->name('delete');
});
