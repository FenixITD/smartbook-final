<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Reviews\CreateReviewController;
use App\Http\Controllers\Api\Reviews\DeleteReviewController;
use App\Http\Controllers\Api\Reviews\GetReviewController;
use App\Http\Controllers\Api\Reviews\GetListReviewController;
use App\Http\Controllers\Api\Reviews\UpdateReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('reviews')->name('api.reviews.')->group(function (): void {
    Route::get('/', GetListReviewController::class)->name('list');
    Route::get('/{review}', GetReviewController::class)->name('show');
    Route::post('/', CreateReviewController::class)->name('create');
    Route::put('/{review}', UpdateReviewController::class)->name('update');
    Route::delete('/{review}', DeleteReviewController::class)->name('delete');
});
