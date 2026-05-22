<?php

use App\Http\Controllers\Web\Genres\CreateGenreController;
use App\Http\Controllers\Web\Genres\DeleteGenreController;
use App\Http\Controllers\Web\Genres\GetByIdGenreController;
use App\Http\Controllers\Web\Genres\GetListGenreController;
use App\Http\Controllers\Web\Genres\UpdateGenreController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureUserIsAdmin::class])->prefix('genres')->name('genres.')->group(function (): void {
    Route::get('/', GetListGenreController::class)->name('index');
    Route::get('/create', [CreateGenreController::class, 'create'])->name('create');
    Route::post('/', [CreateGenreController::class, 'store'])->name('store');
    Route::get('/{genre}', GetByIdGenreController::class)->name('show')->whereNumber('genre');
    Route::get('/{genre}/edit', [UpdateGenreController::class, 'edit'])->name('edit')->whereNumber('genre');
    Route::put('/{genre}', [UpdateGenreController::class, 'update'])->name('update')->whereNumber('genre');
    Route::delete('/{genre}', DeleteGenreController::class)->name('destroy')->whereNumber('genre');
});
