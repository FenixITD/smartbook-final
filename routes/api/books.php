<?php

use App\Http\Controllers\Api\Books\CreateBookController;
use App\Http\Controllers\Api\Books\DeleteBookController;
use App\Http\Controllers\Api\Books\GetBookController;
use App\Http\Controllers\Api\Books\GetListBookController;
use App\Http\Controllers\Api\Books\SearchSuggestCatalogBookController;
use App\Http\Controllers\Api\Books\SearchSuggestController;
use App\Http\Controllers\Api\Books\UpdateBookController;
use Illuminate\Support\Facades\Route;

Route::get('/books/suggest', SearchSuggestController::class)->name('api.books.suggest');
Route::get('/books/catalog-suggest', SearchSuggestCatalogBookController::class)->name('api.books.catalog.suggest');

Route::prefix('books')->name('api.books.')->group(function (): void {
        Route::get('/', GetListBookController::class)->name('list');
        Route::get('/{book}', GetBookController::class)->name('show');
        Route::post('/', CreateBookController::class)->name('create');
        Route::put('/{book}', UpdateBookController::class)->name('update');
        Route::delete('/{book}', DeleteBookController::class)->name('delete');
    });
