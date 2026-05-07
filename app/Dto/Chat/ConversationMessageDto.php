<?php

declare(strict_types=1);

namespace App\Dto\Chat;

final readonly class ConversationMessageDto
{
    /**
     * @param MessageDto[] $messages
     */
    public function __construct(
        public int $conversationId,
        public array $messages,
    ) {
    }
}
