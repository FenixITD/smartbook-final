<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Chat;

use App\Dto\Chat\MessageDto;
use App\Dto\Chat\SendMessageDto;
use App\Http\Controllers\Web\Chat\SendMessageController;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Models\User;
use App\Services\Chat\SendMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class SendMessageControllerTest extends TestCase
{
    private MockInterface&SendMessageService $service;
    private SendMessageController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = Mockery::mock(SendMessageService::class);
        $this->app->instance(SendMessageService::class, $this->service);
        $this->controller = $this->app->make(SendMessageController::class);
    }

    public function test_sends_message_and_returns_201_status(): void
    {
        $user = new User();
        $user->id = 3;
        $user->role = 'admin';

        Auth::shouldReceive('user')->once()->andReturn($user);

        $request = $this->makeRequest(['body' => 'How can I help you?']);

        $messageDto = new MessageDto(
            id: 99,
            conversationId: 10,
            body: 'How can I help you?',
            userId: 3,
            senderName: 'Admin',
            createdAt: '2024-01-01T10:00:00+00:00'
        );

        $this->service
            ->shouldReceive('sendMessage')
            ->once()
            ->with(Mockery::on(function (SendMessageDto $dto) {
                return $dto->conversationId === 10
                    && $dto->userId === 3
                    && $dto->isAdmin === true
                    && $dto->body === 'How can I help you?';
            }))
            ->andReturn($messageDto);

        $response = ($this->controller)($request, 10);

        $this->assertSame(201, $response->getStatusCode());

        $data = json_decode((string) $response->getContent(), true);
        $this->assertSame(99, $data['id']);
        $this->assertSame('How can I help you?', $data['body']);
        $this->assertSame(3, $data['user_id']);
        $this->assertSame('Admin', $data['sender_name']);
    }

    private function makeRequest(array $data): SendMessageRequest
    {
        $request = SendMessageRequest::createFrom(
            Request::create('/chat/conversation/10/messages', 'POST', $data)
        );

        $request->setValidator(
            Validator::make($data, (new SendMessageRequest())->rules())
        );

        return $request;
    }
}
