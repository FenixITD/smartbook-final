<?php

declare(strict_types=1);

namespace App\Dto\Chat;

use App\Http\Requests\Chat\SendMessageRequest;
use App\Models\User;

final readonly class SendMessageDto
{
    public function __construct(
        public int $conversationId,
        public int $userId,
        public bool $isAdmin,
        public string $body,
    ) {
    }

    public static function fromRequest(SendMessageRequest $request, int $conversationId, User $user): self
    {
        /** @var string $body */
        $body = $request->validated('body');

        return new self(
            conversationId: $conversationId,
            userId: $user->id,
            isAdmin: $user->role === 'admin',
            body: $body,
        );
    }
}
