<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Dto\Chat\MessageDto;
use App\Dto\Chat\SendMessageDto;
use App\Events\ConversationCreatedEvent;
use App\Events\MessageSentEvent;
use App\Repositories\Interfaces\ConversationRepositoryInterface;
use App\Repositories\Interfaces\MessageRepositoryInterface;

class SendMessageService
{
    public function __construct(
        private ConversationRepositoryInterface $conversationRepository,
        private MessageRepositoryInterface $messageRepository,
    ) {
    }

    public function sendMessage(SendMessageDto $dto): MessageDto
    {
        if (!$dto->isAdmin) {
            $ownerId = $this->conversationRepository->getOwnerId($dto->conversationId);

            if ($ownerId !== $dto->userId) {
                abort(403, 'Access denied.');
            }
        }

        $messageDto = $this->messageRepository->create($dto->conversationId, $dto->userId, $dto->body);

        $messageCount = $this->conversationRepository->getMessageCount($dto->conversationId);

        if ($messageCount === 1) {
            $summaryDto = $this->conversationRepository->getSummary($dto->conversationId);

            if ($summaryDto) {
                ConversationCreatedEvent::dispatch((array) $summaryDto);
            }
        }

        MessageSentEvent::dispatch($messageDto, $dto->conversationId);

        return $messageDto;
    }
}
