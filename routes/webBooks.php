<?php

use App\Http\Controllers\Web\Books\CreateBookWebController;
use App\Http\Controllers\Web\Books\DeleteBookWebController;
use App\Http\Controllers\Web\Books\GetByIdBookWebController;
use App\Http\Controllers\Web\Books\GetListBookWebController;
use App\Http\Controllers\Web\Books\UpdateBookWebController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('books')->name('books.')->group(function (): void {
    Route::get('/', GetListBookWebController::class)->name('index');
    Route::get('/create', [CreateBookWebController::class, 'create'])->name('create');
    Route::post('/', [CreateBookWebController::class, 'store'])->name('store');
    Route::get('/{book}', GetByIdBookWebController::class)->name('show');
    Route::get('/{book}/edit', [UpdateBookWebController::class, 'edit'])->name('edit');
    Route::put('/{book}', [UpdateBookWebController::class, 'update'])->name('update');
    Route::delete('/{book}', DeleteBookWebController::class)->name('destroy');
});
