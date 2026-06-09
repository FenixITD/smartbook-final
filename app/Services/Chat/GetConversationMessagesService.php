<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Dto\Chat\GetConversationMessagesDto;
use App\Dto\Chat\MessageDto;
use App\Repositories\Interfaces\ConversationRepositoryInterface;
use App\Repositories\Interfaces\MessageRepositoryInterface;

class GetConversationMessagesService
{
    public function __construct(
        private ConversationRepositoryInterface $conversationRepository,
        private MessageRepositoryInterface $messageRepository,
    ) {
    }

    /**
     * @return MessageDto[]
     */
    public function getConversationMessages(GetConversationMessagesDto $dto): array
    {
        if (!$dto->isAdmin) {
            $this->conversationRepository->getOwnerId($dto->conversationId);
        }

        if ($dto->isAdmin) {
            $this->messageRepository->markUserMessagesAsRead($dto->conversationId);
        }

        return $this->conversationRepository->getMessages($dto->conversationId);
    }
}
