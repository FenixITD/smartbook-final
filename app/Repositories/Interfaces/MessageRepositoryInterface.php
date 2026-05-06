<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

interface MessageRepositoryInterface
{
    public function countUnread(int $conversationId): int;
}
