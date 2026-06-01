<?php

declare(strict_types=1);

use App\Http\Controllers\Web\Books\CreateBookController;
use App\Http\Controllers\Web\Books\DeleteBookController;
use App\Http\Controllers\Web\Books\GetByIdBookController;
use App\Http\Controllers\Web\Books\GetListBookController;
use App\Http\Controllers\Web\Books\UpdateBookController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureUserIsAdmin::class])->prefix('books')->name('books.')->group(function (): void {
    Route::get('/', GetListBookController::class)->name('index');
    Route::get('/create', [CreateBookController::class, 'create'])->name('create');
    Route::post('/', [CreateBookController::class, 'store'])->name('store');
    Route::get('/{book}', GetByIdBookController::class)->name('show')->whereNumber('book');
    Route::get('/{book}/edit', [UpdateBookController::class, 'edit'])->name('edit')->whereNumber('book');
    Route::put('/{book}', [UpdateBookController::class, 'update'])->name('update')->whereNumber('book');
    Route::delete('/{book}', DeleteBookController::class)->name('destroy')->whereNumber('book');
});
