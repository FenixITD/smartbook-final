<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Dto\Chat\ConversationMessageDto;
use App\Dto\Chat\ConversationSummaryDto;
use App\Dto\Chat\GetConversationMessagesDto;
use App\Dto\Chat\MessageDto;
use App\Dto\Chat\SendMessageDto;
use App\Events\MessageSent;
use App\Repositories\Interfaces\ConversationRepositoryInterface;
use App\Repositories\Interfaces\MessageRepositoryInterface;

final class ChatService
{
    public function __construct(
        private ConversationRepositoryInterface $conversationRepository,
        private MessageRepositoryInterface $messageRepository,
    ) {
    }

    /**
     * @return ConversationSummaryDto[]
     */
    public function getAdminConversations(): array
    {
        return $this->conversationRepository->getAllWithUnreadCounts();
    }

    public function openConversation(int $userId, int $bookId): ConversationMessageDto
    {
        $conversationId = $this->conversationRepository->findOrCreateByUserAndBook($userId, $bookId);
        $messages = $this->conversationRepository->getMessages($conversationId);

        return new ConversationMessageDto(
            conversationId: $conversationId,
            messages: $messages,
        );
    }

    /**
     * @return MessageDto[]
     */
    public function getConversationMessages(GetConversationMessagesDto $dto): array
    {
        if (!$dto->isAdmin) {
            $this->assertOwnership($dto->conversationId, $dto->userId);
        }

        return $this->conversationRepository->getMessages($dto->conversationId);
    }

    public function sendMessage(SendMessageDto $dto): MessageDto
    {
        if (!$dto->isAdmin) {
            $this->assertOwnership($dto->conversationId, $dto->userId);
        }

        $messageDto = $this->messageRepository->create($dto->conversationId, $dto->userId, $dto->body);

        MessageSent::dispatch($messageDto, $dto->conversationId);

        return $messageDto;
    }

    private function assertOwnership(int $conversationId, int $userId): void
    {
        $ownerId = $this->conversationRepository->getOwnerId($conversationId);

        if ($ownerId !== $userId) {
            abort(403, 'Access denied.');
        }
    }
}
