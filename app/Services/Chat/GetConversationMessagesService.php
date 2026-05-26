<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Dto\Chat\GetConversationMessagesDto;
use App\Dto\Chat\MessageDto;
use App\Repositories\Interfaces\ConversationRepositoryInterface;
use App\Repositories\Interfaces\MessageRepositoryInterface;

final class GetConversationMessagesService
{
    public function __construct(
        private ConversationRepositoryInterface $conversationRepository,
        private MessageRepositoryInterface $messageRepository,
    ) {
    }

    /**
     * @param GetConversationMessagesDto $dto
     * @return MessageDto[]
     *
     * Retrieves messages for a specific conversation. Validates ownership for non-admin users and marks messages as read if viewed by an admin.
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
