<?php

use App\Http\Controllers\Web\Reviews\CreateReviewWebController;
use App\Http\Controllers\Web\Reviews\DeleteReviewWebController;
use App\Http\Controllers\Web\Reviews\GetByIdReviewWebController;
use App\Http\Controllers\Web\Reviews\GetListReviewWebController;
use App\Http\Controllers\Web\Reviews\UpdateReviewWebController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('reviews')->name('reviews.')->group(function (): void {
    Route::get('/', GetListReviewWebController::class)->name('index');
    Route::get('/create', [CreateReviewWebController::class, 'create'])->name('create');
    Route::post('/', [CreateReviewWebController::class, 'store'])->name('store');
    Route::get('/{review}', GetByIdReviewWebController::class)->name('show')->whereNumber('review');
    Route::get('/{review}/edit', [UpdateReviewWebController::class, 'edit'])->name('edit')->whereNumber('review');
    Route::put('/{review}', [UpdateReviewWebController::class, 'update'])->name('update')->whereNumber('review');
    Route::delete('/{review}', DeleteReviewWebController::class)->name('destroy')->whereNumber('review');
});
