<?php

use App\Http\Controllers\Reviews\CreateReviewController;
use App\Http\Controllers\Reviews\DeleteReviewController;
use App\Http\Controllers\Reviews\GetByIdReviewController;
use App\Http\Controllers\Reviews\GetListReviewController;
use App\Http\Controllers\Reviews\UpdateReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('reviews')->group(function (): void {
    Route::get('/', GetListReviewController::class)->name('reviews.list');
    Route::get('/{review}', GetByIdReviewController::class)->name('reviews.show');
    Route::post('/', CreateReviewController::class)->name('reviews.create');
    Route::put('/{review}', UpdateReviewController::class)->name('reviews.update');
    Route::delete('/{review}', DeleteReviewController::class)->name('reviews.delete');
});
