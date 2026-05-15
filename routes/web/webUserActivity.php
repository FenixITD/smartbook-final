<?php

declare(strict_types=1);

use App\Http\Controllers\Web\UserActivity\GetUserActivityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('user-activity')->name('user-activity.')->group(function (): void {
        Route::get('/', GetUserActivityController::class)->name('index');
    });
