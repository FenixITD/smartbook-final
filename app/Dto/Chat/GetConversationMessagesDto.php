<?php

declare(strict_types=1);

namespace App\Dto\Chat;

use App\Models\User;

final readonly class GetConversationMessagesDto
{
    public function __construct(
        public int $conversationId,
        public int $userId,
        public bool $isAdmin,
    ) {
    }

    public static function fromUser(int $conversationId, User $user): self
    {
        return new self(
            conversationId: $conversationId,
            userId: $user->id,
            isAdmin: $user->role === 'admin',
        );
    }
}
