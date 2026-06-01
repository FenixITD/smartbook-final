<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Chat;

use App\Dto\Chat\MessageDto;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class MessageDtoTest extends TestCase
{
    public function test_from_model_creates_dto_and_returns_array(): void
    {
        $user = new User();
        $user->name = 'Charlie';

        $message = new Message();
        $message->id = 99;
        $message->conversation_id = 5;
        $message->body = 'Test message';
        $message->user_id = 12;
        $message->created_at = Carbon::parse('2026-06-01 12:00:00');

        $message->setRelation('user', $user);

        $dto = MessageDto::fromModel($message);

        $this->assertSame(99, $dto->id);
        $this->assertSame(5, $dto->conversationId);
        $this->assertSame('Test message', $dto->body);
        $this->assertSame(12, $dto->userId);
        $this->assertSame('Charlie', $dto->senderName);
        $this->assertSame('2026-06-01T12:00:00+03:00', $dto->createdAt);

        $this->assertSame([
            'id' => 99,
            'body' => 'Test message',
            'user_id' => 12,
            'sender_name' => 'Charlie',
            'created_at' => '2026-06-01T12:00:00+03:00',
        ], $dto->toArray());
    }

    public function test_from_model_handles_null_created_at(): void
    {
        $user = new User();
        $user->name = 'Dave';

        $message = new Message();
        $message->id = 100;
        $message->conversation_id = 6;
        $message->body = 'Another message';
        $message->user_id = 15;
        $message->created_at = null;

        $message->setRelation('user', $user);

        $dto = MessageDto::fromModel($message);

        $this->assertNull($dto->createdAt);

        $this->assertSame([
            'id' => 100,
            'body' => 'Another message',
            'user_id' => 15,
            'sender_name' => 'Dave',
            'created_at' => null,
        ], $dto->toArray());
    }
}
