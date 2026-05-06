<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Message;
use App\Repositories\Interfaces\MessageRepositoryInterface;

final class MessageRepository implements MessageRepositoryInterface
{
    public function countUnread(int $conversationId): int
    {
        return Message::where('conversation_id', $conversationId)
            ->whereNull('read_at')
            ->whereIn('user_id', static function ($query) use ($conversationId): void {
                $query->select('user_id')
                    ->from('conversations')
                    ->where('id', $conversationId);
            })
            ->count();
    }
}
