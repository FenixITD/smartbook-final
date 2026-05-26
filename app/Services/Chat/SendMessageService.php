<?php

namespace App\Services\Chat;

use App\Dto\Chat\MessageDto;
use App\Dto\Chat\SendMessageDto;
use App\Events\MessageSentEvent;
use App\Repositories\Interfaces\ConversationRepositoryInterface;
use App\Repositories\Interfaces\MessageRepositoryInterface;

final class SendMessageService
{
    public function __construct(
        private ConversationRepositoryInterface $conversationRepository,
        private MessageRepositoryInterface $messageRepository,
    ) {
    }

    /**
     * @param SendMessageDto $dto
     * @return MessageDto
     *
     * Dispatches a new message in a conversation, validates ownership for regular users, and triggers a message sent event.
     */
    public function sendMessage(SendMessageDto $dto): MessageDto
    {
        if (!$dto->isAdmin) {
            $this->conversationRepository->getOwnerId($dto->conversationId);
        }

        $messageDto = $this->messageRepository->create($dto->conversationId, $dto->userId, $dto->body);

        MessageSentEvent::dispatch($messageDto, $dto->conversationId);

        return $messageDto;
    }
}
