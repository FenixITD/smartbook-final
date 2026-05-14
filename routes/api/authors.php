<?php

use App\Http\Controllers\Api\Authors\CreateAuthorController;
use App\Http\Controllers\Api\Authors\DeleteAuthorController;
use App\Http\Controllers\Api\Authors\GetAuthorController;
use App\Http\Controllers\Api\Authors\GetListAuthorController;
use App\Http\Controllers\Api\Authors\SearchSuggestController;
use App\Http\Controllers\Api\Authors\UpdateAuthorController;
use Illuminate\Support\Facades\Route;

Route::get('/authors/suggest', SearchSuggestController::class)->name('api.authors.suggest');

Route::prefix('authors')->name('api.authors.')->group(function (): void {
    Route::get('/suggest', SearchSuggestController::class)->name('suggest');
    Route::get('/', GetListAuthorController::class)->name('index');
    Route::post('/', CreateAuthorController::class)->name('store');
    Route::get('/{author}', GetAuthorController::class)->name('show');
    Route::put('/{author}', UpdateAuthorController::class)->name('update');
    Route::delete('/{author}', DeleteAuthorController::class)->name('destroy');
});
