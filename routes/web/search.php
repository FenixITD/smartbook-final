<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Books\SearchSuggestCatalogBookController;
use App\Http\Controllers\Api\Books\SearchSuggestController;
use App\Http\Controllers\Api\Authors\SearchSuggestController as AuthorsSearchSuggestController;
use App\Http\Controllers\Api\Genres\SearchSuggestController as GenresSearchSuggestController;
use App\Http\Controllers\Api\Orders\SearchSuggestController as OrdersSearchSuggestController;
use App\Http\Controllers\Api\Reviews\SearchSuggestController as ReviewsSearchSuggestController;
use App\Http\Middleware\EnsureUserIsAdmin;

Route::get('/books/catalog-suggest', SearchSuggestCatalogBookController::class)->name('api.books.catalog.suggest');

Route::middleware(['auth', EnsureUserIsAdmin::class])->group(function (): void {
    Route::get('/books/suggest', SearchSuggestController::class)->name('api.books.suggest');
    Route::get('/authors/suggest', AuthorsSearchSuggestController::class)->name('api.authors.suggest');
    Route::get('/genres/suggest', GenresSearchSuggestController::class)->name('api.genres.suggest');
    Route::get('/orders/suggest', OrdersSearchSuggestController::class)->name('api.orders.suggest');
    Route::get('/reviews/suggest', ReviewsSearchSuggestController::class)->name('api.reviews.suggest');
});
