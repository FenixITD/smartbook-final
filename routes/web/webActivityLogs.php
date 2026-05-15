<?php

declare(strict_types=1);

use App\Http\Controllers\Web\ActivityLog\GetActivityLogController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureUserIsAdmin::class])->prefix('activity-logs')->name('activity-logs.')
    ->group(function (): void {
        Route::get('/', GetActivityLogController::class)->name('index');
    });
