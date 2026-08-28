<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Chat;

use App\Dto\Chat\ConversationMessageDto;
use App\Dto\Chat\MessageDto;
use Tests\TestCase;

final class ConversationMessageDtoTest extends TestCase
{
    public function test_conversation_message_dto_initializes_correctly(): void
    {
        $message = new MessageDto(
            1,
            5,
            'Hello',
            10,
            'John Doe',
            '2026-06-01T10:00:00+00:00'
        );

        $dto = new ConversationMessageDto(5, [$message], 'open');

        $this->assertSame(5, $dto->conversationId);
        $this->assertSame('open', $dto->status);
        $this->assertCount(1, $dto->messages);
        $this->assertSame($message, $dto->messages[0]);
    }

    public function test_conversation_message_dto_keeps_status(): void
    {
        $dto = new ConversationMessageDto(5, [], 'closed');

        $this->assertSame('closed', $dto->status);
    }
}
