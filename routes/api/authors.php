<?php

use App\Http\Controllers\Api\Authors\CreateAuthorController;
use App\Http\Controllers\Api\Authors\DeleteAuthorController;
use App\Http\Controllers\Api\Authors\GetByIdAuthorController;
use App\Http\Controllers\Api\Authors\GetListAuthorController;
use App\Http\Controllers\Api\Authors\UpdateAuthorController;
use Illuminate\Support\Facades\Route;

Route::prefix('authors')->name('api.authors.')->group(function (): void {
    Route::get('/', GetListAuthorController::class)->name('index');
    Route::post('/', CreateAuthorController::class)->name('store');
    Route::get('/{author}', GetByIdAuthorController::class)->name('show');
    Route::put('/{author}', UpdateAuthorController::class)->name('update');
    Route::delete('/{author}', DeleteAuthorController::class)->name('destroy');
});
