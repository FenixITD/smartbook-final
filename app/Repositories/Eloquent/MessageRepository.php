<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\Chat\MessageDto;
use App\Models\Message;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use Illuminate\Database\Query\Builder;

final class MessageRepository implements MessageRepositoryInterface
{
    public function countUnread(int $conversationId): int
    {
        return Message::where('conversation_id', $conversationId)
            ->whereNull('read_at')
            ->whereIn('user_id', static function (Builder $query) use ($conversationId): void {
                $query->select('user_id')
                    ->from('conversations')
                    ->where('id', $conversationId);
            })
            ->count();
    }

    public function create(int $conversationId, int $userId, string $body): MessageDto
    {
        $message = Message::create([
            'conversation_id' => $conversationId,
            'user_id' => $userId,
            'body' => $body,
        ]);

        $message->load('user:id,name');

        return MessageDto::fromModel($message);
    }

    public function markUserMessagesAsRead(int $conversationId): void
    {
        Message::where('conversation_id', $conversationId)
            ->whereNull('read_at')
            ->whereIn('user_id', static function (Builder $query) use ($conversationId): void {
                $query->select('user_id')
                    ->from('conversations')
                    ->where('id', $conversationId);
            })
            ->update(['read_at' => now()]);
    }
}
