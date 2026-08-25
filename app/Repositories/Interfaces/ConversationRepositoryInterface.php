<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\Chat\ConversationSummaryDto;
use App\Dto\Chat\MessageDto;

interface ConversationRepositoryInterface
{
    /**
     * @return ConversationSummaryDto[]
     */
    public function getAllWithUnreadCounts(): array;

    public function getOwnerId(int $conversationId): int|null;

    public function findOrCreateByUserAndBook(int $userId, int $bookId): int;

    /**
     * @return MessageDto[]
     */
    public function getMessages(int $conversationId): array;

    public function getTotalUnreadCount(): int;

    public function close(int $conversationId): void;

    public function getMessageCount(int $conversationId): int;

    public function getSummary(int $conversationId): ConversationSummaryDto|null;

    public function getStatus(int $conversationId): string|null;
}
