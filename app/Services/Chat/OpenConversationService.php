<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Dto\Chat\ConversationMessageDto;
use App\Repositories\Interfaces\ConversationRepositoryInterface;

class OpenConversationService
{
    public function __construct(
        private ConversationRepositoryInterface $conversationRepository,
    ) {
    }

    /**
     * @return ConversationMessageDto
     */
    public function openConversation(int $userId, int $bookId): ConversationMessageDto
    {
        $conversationId = $this->conversationRepository->findOrCreateByUserAndBook($userId, $bookId);
        $messages = $this->conversationRepository->getMessages($conversationId);

        return new ConversationMessageDto(
            conversationId: $conversationId,
            messages: $messages,
        );
    }
}
