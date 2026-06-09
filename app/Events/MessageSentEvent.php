<?php

declare(strict_types=1);

namespace App\Events;

use App\Dto\Chat\MessageDto;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class MessageSentEvent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public MessageDto $message,
        public int $conversationId,
    ) {
    }

    /**
     * The ShouldBroadcast interface (and its extension, ShouldBroadcastNow) requires the implementation
     * of only one method: broadcastOn(). The broadcastWith and broadcastAs methods are optional and are therefore
     * annotated with @noinspection PhpUnused. Since they are not specified as mandatory in the interface itself,
     * the IDE does not know that they implement any contract or are used by the framework core.
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('conversation.'.$this->conversationId);
    }

    /** @return array<string, mixed>
     * @noinspection PhpUnused
     */
    public function broadcastWith(): array
    {
        return $this->message->toArray();
    }

    /** @noinspection PhpUnused */
    public function broadcastAs(): string
    {
        return 'MessageSent';
    }
}
