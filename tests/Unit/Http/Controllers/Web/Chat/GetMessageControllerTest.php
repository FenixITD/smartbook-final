<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Chat;

use App\Dto\Chat\GetConversationMessagesDto;
use App\Dto\Chat\MessageDto;
use App\Http\Controllers\Web\Chat\GetMessageController;
use App\Models\User;
use App\Services\Chat\GetConversationMessagesService;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class GetMessageControllerTest extends TestCase
{
    private MockInterface&GetConversationMessagesService $service;
    private GetMessageController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = Mockery::mock(GetConversationMessagesService::class);
        $this->app->instance(GetConversationMessagesService::class, $this->service);
        $this->controller = $this->app->make(GetMessageController::class);
    }

    public function test_aborts_with_401_when_user_is_null(): void
    {
        Auth::shouldReceive('user')->once()->andReturn(null);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(''); // abort(401) sets empty message

        try {
            ($this->controller)(10);
        } catch (HttpException $e) {
            $this->assertSame(401, $e->getStatusCode());
            throw $e;
        }
    }

    public function test_returns_messages_for_authenticated_user(): void
    {
        $user = new User();
        $user->id = 5;
        $user->role = 'user';

        Auth::shouldReceive('user')->once()->andReturn($user);

        $messageDto = new MessageDto(
            id: 1,
            conversationId: 10,
            body: 'Hello World',
            userId: 5,
            senderName: 'John Doe',
            createdAt: '2024-01-01T12:00:00+00:00'
        );

        $this->service
            ->shouldReceive('getConversationMessages')
            ->once()
            ->with(Mockery::on(function (GetConversationMessagesDto $dto) {
                return $dto->conversationId === 10
                    && $dto->userId === 5
                    && $dto->isAdmin === false;
            }))
            ->andReturn([$messageDto]);

        $response = ($this->controller)(10);

        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode((string) $response->getContent(), true);
        $this->assertArrayHasKey('messages', $data);
        $this->assertCount(1, $data['messages']);
        $this->assertSame('Hello World', $data['messages'][0]['body']);
    }
}
