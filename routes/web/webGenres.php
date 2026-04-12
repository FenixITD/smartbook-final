<?php

use App\Http\Controllers\Web\Genres\CreateGenreWebController;
use App\Http\Controllers\Web\Genres\DeleteGenreWebController;
use App\Http\Controllers\Web\Genres\GetGenreWebController;
use App\Http\Controllers\Web\Genres\GetListGenreWebController;
use App\Http\Controllers\Web\Genres\UpdateGenreWebController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('genres')->name('genres.')->group(function (): void {
    Route::get('/', GetListGenreWebController::class)->name('index');
    Route::get('/create', [CreateGenreWebController::class, 'create'])->name('create');
    Route::post('/', [CreateGenreWebController::class, 'store'])->name('store');
    Route::get('/{genre}', GetGenreWebController::class)->name('show')->whereNumber('genre');
    Route::get('/{genre}/edit', [UpdateGenreWebController::class, 'edit'])->name('edit')->whereNumber('genre');
    Route::put('/{genre}', [UpdateGenreWebController::class, 'update'])->name('update')->whereNumber('genre');
    Route::delete('/{genre}', DeleteGenreWebController::class)->name('destroy')->whereNumber('genre');
});
