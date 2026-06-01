<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Chat;

use App\Dto\Chat\SendMessageDto;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Models\User;
use Mockery;
use Tests\TestCase;

final class SendMessageDtoTest extends TestCase
{
    public function test_from_request_creates_dto_for_admin(): void
    {
        $request = Mockery::mock(SendMessageRequest::class);
        $request->shouldReceive('validated')
            ->once()
            ->with('body')
            ->andReturn('Admin response');

        $user = new User();
        $user->id = 1;
        $user->role = 'admin';

        $dto = SendMessageDto::fromRequest($request, 42, $user);

        $this->assertSame(42, $dto->conversationId);
        $this->assertSame(1, $dto->userId);
        $this->assertTrue($dto->isAdmin);
        $this->assertSame('Admin response', $dto->body);
    }

    public function test_from_request_creates_dto_for_regular_user(): void
    {
        $request = Mockery::mock(SendMessageRequest::class);
        $request->shouldReceive('validated')
            ->once()
            ->with('body')
            ->andReturn('User message');

        $user = new User();
        $user->id = 7;
        $user->role = 'user';

        $dto = SendMessageDto::fromRequest($request, 50, $user);

        $this->assertSame(50, $dto->conversationId);
        $this->assertSame(7, $dto->userId);
        $this->assertFalse($dto->isAdmin);
        $this->assertSame('User message', $dto->body);
    }
}
