<?php

use App\Http\Controllers\Web\Reviews\CreateReviewController;
use App\Http\Controllers\Web\Reviews\DeleteReviewController;
use App\Http\Controllers\Web\Reviews\GetReviewController;
use App\Http\Controllers\Web\Reviews\GetListReviewController;
use App\Http\Controllers\Web\Reviews\UpdateReviewController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureUserIsAdmin::class])->prefix('reviews')->name('reviews.')->group(function (): void {
    Route::get('/', GetListReviewController::class)->name('index');
    Route::get('/create', [CreateReviewController::class, 'create'])->name('create');
    Route::post('/', [CreateReviewController::class, 'store'])->name('store');
    Route::get('/{review}', GetReviewController::class)->name('show')->whereNumber('review');
    Route::get('/{review}/edit', [UpdateReviewController::class, 'edit'])->name('edit')->whereNumber('review');
    Route::put('/{review}', [UpdateReviewController::class, 'update'])->name('update')->whereNumber('review');
    Route::delete('/{review}', DeleteReviewController::class)->name('destroy')->whereNumber('review');
});
