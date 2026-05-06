<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// ShouldBroadcast — интерфейс, который говорит Laravel:
// "отправь это событие через WebSocket (Reverb), а не только внутри PHP"
class MessageSent implements ShouldBroadcast
{
    // Эти трейты добавляют нужную инфраструктуру для событий и очередей
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    // Данные, которые будут отправлены клиенту через WebSocket.
    // public свойства автоматически сериализуются в JSON.
    public function __construct(
        public readonly Message $message,
    ) {
    }

    // На каком канале транслировать событие.
    // PrivateChannel = только авторизованные пользователи могут подписаться.
    // Имя канала: "conversation.1", "conversation.2" и т.д.
    // Авторизация канала описана в routes/channels.php
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('conversation.'.$this->message->conversation_id);
    }

    // Какие данные отправить клиенту.
    // Мы явно указываем поля, чтобы не слать лишнее.
    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'body' => $this->message->body,
            'user_id' => $this->message->user_id,
            'sender_name' => $this->message->user->name,
            'created_at' => $this->message->created_at?->toIso8601String(),
        ];
    }

    // Название события на фронте (по умолчанию — имя класса).
    // Явно задаём, чтобы фронт слушал '.MessageSent'
    public function broadcastAs(): string
    {
        return 'MessageSent';
    }
}
