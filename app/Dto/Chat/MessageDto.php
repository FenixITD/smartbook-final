<?php

declare(strict_types=1);

namespace App\Dto\Chat;

use App\Models\Message;
use App\Models\User;

final readonly class MessageDto
{
    public function __construct(
        public int $id,
        public int $conversationId,
        public string $body,
        public int $userId,
        public string $senderName,
        public string|null $createdAt,
    ) {
    }

    public static function fromModel(Message $message): self
    {
        /** @var User $user */
        $user = $message->user;

        return new self(
            id: $message->id,
            conversationId: $message->conversation_id,
            body: $message->body,
            userId: $message->user_id,
            senderName: $user->name,
            createdAt: $message->created_at?->toIso8601String(),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'user_id' => $this->userId,
            'sender_name' => $this->senderName,
            'created_at' => $this->createdAt,
        ];
    }
}
