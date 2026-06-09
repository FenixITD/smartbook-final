<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Dto\Chat\MessageDto;
use App\Dto\Chat\SendMessageDto;
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

    /**
     * @return MessageDto
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
