<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ConversationCreatedEvent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param array<string, mixed> $conversation
     */
    public function __construct(
        public array $conversation,
    ) {
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('admin.conversations');
    }

    /**
     * @noinspection PhpUnused
     */
    public function broadcastAs(): string
    {
        return 'ConversationCreated';
    }
}
