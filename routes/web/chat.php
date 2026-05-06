<?php

declare(strict_types=1);

use App\Http\Controllers\Web\Chat\AdminConversationController;
use App\Http\Controllers\Web\Chat\GetMessageController;
use App\Http\Controllers\Web\Chat\OpenConversationController;
use App\Http\Controllers\Web\Chat\SendMessageController;
use Illuminate\Support\Facades\Route;

// Все маршруты чата требуют авторизации — auth middleware
Route::middleware(['auth'])->prefix('chat')->name('chat.')->group(function (): void {

    // POST /chat/book/{book} — открыть или создать диалог по книге
    // Возвращает JSON с conversation_id и историей сообщений
    Route::post('/book/{book}', OpenConversationController::class)
        ->name('open')
        ->whereNumber('book');

    // GET  /chat/conversation/{conversation}/messages — получить историю сообщений
    Route::get('/conversation/{conversation}/messages', GetMessageController::class)
        ->name('messages.index');

    // POST /chat/conversation/{conversation}/messages — отправить сообщение
    // {conversation} — Laravel автоматически найдёт запись в БД (Route Model Binding)
    Route::post('/conversation/{conversation}/messages', SendMessageController::class)
        ->name('messages.store');

    // Маршруты только для администратора
    // Middleware 'admin' — создадим его далее
    Route::middleware(['admin'])->group(function (): void {
        // GET /chat/admin — список всех диалогов
        Route::get('/admin', AdminConversationController::class)->name('admin');
    });
});
