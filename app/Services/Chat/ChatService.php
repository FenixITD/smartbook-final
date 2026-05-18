<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Dto\Chat\ConversationMessageDto;
use App\Dto\Chat\ConversationSummaryDto;
use App\Dto\Chat\GetConversationMessagesDto;
use App\Dto\Chat\MessageDto;
use App\Dto\Chat\SendMessageDto;
use App\Events\MessageSentEvent;
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
     *
     * Retrieves all conversations along with their unread message counts for the admin panel.
     */
    public function getAdminConversations(): array
    {
        return $this->conversationRepository->getAllWithUnreadCounts();
    }

    /**
     * @param int $userId
     * @param int $bookId
     * @return ConversationMessageDto
     *
     * Finds an existing conversation or creates a new one between a user and admin regarding a specific book, returning the conversation details and its messages.
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

    /**
     * @param GetConversationMessagesDto $dto
     * @return MessageDto[]
     *
     * Retrieves messages for a specific conversation. Validates ownership for non-admin users and marks messages as read if viewed by an admin.
     */
    public function getConversationMessages(GetConversationMessagesDto $dto): array
    {
        if (!$dto->isAdmin) {
            $this->assertOwnership($dto->conversationId, $dto->userId);
        }

        if ($dto->isAdmin) {
            $this->messageRepository->markUserMessagesAsRead($dto->conversationId);
        }

        return $this->conversationRepository->getMessages($dto->conversationId);
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
            $this->assertOwnership($dto->conversationId, $dto->userId);
        }

        $messageDto = $this->messageRepository->create($dto->conversationId, $dto->userId, $dto->body);

        MessageSentEvent::dispatch($messageDto, $dto->conversationId);

        return $messageDto;
    }

    /**
     * @param int $conversationId
     * @param int $userId
     * @return void
     *
     * Ensures that the specified user is the owner of the given conversation, aborting with a 403 error if not.
     */
    private function assertOwnership(int $conversationId, int $userId): void
    {
        $ownerId = $this->conversationRepository->getOwnerId($conversationId);

        if ($ownerId !== $userId) {
            abort(403, 'Access denied.');
        }
    }

    /**
     * @param int $conversationId
     * @return void
     *
     * Sets the chat status with the user to "closed"
     */
    public function closeConversation(int $conversationId): void
    {
        $this->conversationRepository->close($conversationId);
    }
}
