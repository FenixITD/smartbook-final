<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Chat;

use App\Dto\Chat\GetConversationMessagesDto;
use App\Models\User;
use Tests\TestCase;

final class GetConversationMessagesDtoTest extends TestCase
{
    public function test_from_user_creates_dto_for_admin(): void
    {
        $user = new User();
        $user->id = 1;
        $user->role = 'admin';

        $dto = GetConversationMessagesDto::fromUser(100, $user);

        $this->assertSame(100, $dto->conversationId);
        $this->assertSame(1, $dto->userId);
        $this->assertTrue($dto->isAdmin);
    }

    public function test_from_user_creates_dto_for_regular_user(): void
    {
        $user = new User();
        $user->id = 5;
        $user->role = 'user';

        $dto = GetConversationMessagesDto::fromUser(105, $user);

        $this->assertSame(105, $dto->conversationId);
        $this->assertSame(5, $dto->userId);
        $this->assertFalse($dto->isAdmin);
    }
}
