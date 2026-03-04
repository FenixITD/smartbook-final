<?php

use App\Http\Controllers\Books\CreateBookController;
use App\Http\Controllers\Books\DeleteBookController;
use App\Http\Controllers\Books\GetByIdBookController;
use App\Http\Controllers\Books\GetListBookController;
use App\Http\Controllers\Books\UpdateBookController;
use Illuminate\Support\Facades\Route;

Route::prefix('books')->group(function (): void {
    Route::get('/', GetListBookController::class)->name('books.list');
    Route::get('/{book}', GetByIdBookController::class)->name('books.show');
    Route::post('/', CreateBookController::class)->name('books.create');
    Route::put('/{book}', UpdateBookController::class)->name('books.update');
    Route::delete('/{book}', DeleteBookController::class)->name('books.delete');
});
