<?php

declare(strict_types=1);

use App\Http\Controllers\Web\Chat\AdminConversationController;
use App\Http\Controllers\Web\Chat\CloseConversationController;
use App\Http\Controllers\Web\Chat\GetMessageController;
use App\Http\Controllers\Web\Chat\OpenConversationController;
use App\Http\Controllers\Web\Chat\SendMessageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('chat')->name('chat.')->group(function (): void {
    Route::post('/book/{book}', OpenConversationController::class)->name('open')->whereNumber('book');
    Route::get('/conversation/{conversation}/messages', GetMessageController::class)->name('messages.index');
    Route::post('/conversation/{conversation}/messages', SendMessageController::class)->name('messages.store');
    Route::middleware(['admin'])->group(function (): void {
        Route::get('/admin', AdminConversationController::class)->name('admin');
        Route::patch('/conversation/{conversation}/close', CloseConversationController::class)->name('conversation.close');
    });
});
