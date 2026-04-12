<?php

use App\Http\Controllers\Web\Authors\CreateAuthorWebController;
use App\Http\Controllers\Web\Authors\DeleteAuthorWebController;
use App\Http\Controllers\Web\Authors\GetAuthorWebController;
use App\Http\Controllers\Web\Authors\GetListAuthorWebController;
use App\Http\Controllers\Web\Authors\UpdateAuthorWebController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('authors')->name('authors.')->group(function (): void {
    Route::get('/', GetListAuthorWebController::class)->name('index');
    Route::get('/create', [CreateAuthorWebController::class, 'create'])->name('create');
    Route::post('/', [CreateAuthorWebController::class, 'store'])->name('store');
    Route::get('/{author}', GetAuthorWebController::class)->name('show')->whereNumber('author');
    Route::get('/{author}/edit', [UpdateAuthorWebController::class, 'edit'])->name('edit')->whereNumber('author');
    Route::put('/{author}', [UpdateAuthorWebController::class, 'update'])->name('update')->whereNumber('author');
    Route::delete('/{author}', DeleteAuthorWebController::class)->name('destroy')->whereNumber('author');
});
