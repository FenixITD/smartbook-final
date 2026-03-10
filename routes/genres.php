<?php

use App\Http\Controllers\Genres\CreateGenreController;
use App\Http\Controllers\Genres\DeleteGenreController;
use App\Http\Controllers\Genres\GetByIdGenreController;
use App\Http\Controllers\Genres\GetListGenreController;
use App\Http\Controllers\Genres\UpdateGenreController;
use Illuminate\Support\Facades\Route;

Route::prefix('genres')->group(function (): void {
    Route::get('/', GetListGenreController::class)->name('genres.list');
    Route::get('/{genre}', GetByIdGenreController::class)->name('genres.show');
    Route::post('/', CreateGenreController::class)->name('genres.create');
    Route::put('/{genre}', UpdateGenreController::class)->name('genres.update');
    Route::delete('/{genre}', DeleteGenreController::class)->name('genres.delete');
});
