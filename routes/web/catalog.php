<?php

use App\Http\Controllers\Web\Catalog\GetPublicBookController;
use App\Http\Controllers\Web\Reviews\StorePublicReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/catalog/{book}', GetPublicBookController::class)->name('catalog.show')->whereNumber('book');

Route::middleware('auth')->group(function (): void {
    Route::post('/catalog/reviews', StorePublicReviewController::class)->name('catalog.reviews.store');
});
