<?php

use App\Http\Controllers\Api\Genres\CreateGenreController;
use App\Http\Controllers\Api\Genres\DeleteGenreController;
use App\Http\Controllers\Api\Genres\GetByIdGenreController;
use App\Http\Controllers\Api\Genres\GetListGenreController;
use App\Http\Controllers\Api\Genres\UpdateGenreController;
use Illuminate\Support\Facades\Route;

Route::prefix('genres')->name('api.genres.')->group(function (): void {
    Route::get('/', GetListGenreController::class)->name('list');
    Route::get('/{genre}', GetByIdGenreController::class)->name('show');
    Route::post('/', CreateGenreController::class)->name('create');
    Route::put('/{genre}', UpdateGenreController::class)->name('update');
    Route::delete('/{genre}', DeleteGenreController::class)->name('delete');
});
