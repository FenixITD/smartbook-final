<?php

use App\Http\Controllers\Web\Authors\CreateAuthorController;
use App\Http\Controllers\Web\Authors\DeleteAuthorController;
use App\Http\Controllers\Web\Authors\GetByIdAuthorController;
use App\Http\Controllers\Web\Authors\GetListAuthorController;
use App\Http\Controllers\Web\Authors\UpdateAuthorController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureUserIsAdmin::class])->prefix('authors')->name('authors.')->group(function (): void {
    Route::get('/', GetListAuthorController::class)->name('index');
    Route::get('/create', [CreateAuthorController::class, 'create'])->name('create');
    Route::post('/', [CreateAuthorController::class, 'store'])->name('store');
    Route::get('/{author}', GetByIdAuthorController::class)->name('show')->whereNumber('author');
    Route::get('/{author}/edit', [UpdateAuthorController::class, 'edit'])->name('edit')->whereNumber('author');
    Route::put('/{author}', [UpdateAuthorController::class, 'update'])->name('update')->whereNumber('author');
    Route::delete('/{author}', DeleteAuthorController::class)->name('destroy')->whereNumber('author');
});
