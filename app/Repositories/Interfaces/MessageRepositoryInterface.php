<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\Chat\MessageDto;

interface MessageRepositoryInterface
{
    public function countUnread(int $conversationId): int;

    public function create(int $conversationId, int $userId, string $body): MessageDto;
}
