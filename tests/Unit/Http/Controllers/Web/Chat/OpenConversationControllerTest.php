<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Chat;

use App\Dto\Chat\ConversationMessageDto;
use App\Dto\Chat\MessageDto;
use App\Http\Controllers\Web\Chat\OpenConversationController;
use App\Services\Chat\OpenConversationService;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class OpenConversationControllerTest extends TestCase
{
    private MockInterface&OpenConversationService $service;
    private OpenConversationController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = Mockery::mock(OpenConversationService::class);
        $this->app->instance(OpenConversationService::class, $this->service);
        $this->controller = $this->app->make(OpenConversationController::class);
    }

    public function test_returns_json_with_conversation_id_and_messages(): void
    {
        Auth::shouldReceive('id')->once()->andReturn(7);

        $messageDto = new MessageDto(
            id: 1,
            conversationId: 42,
            body: 'I have a question about this book',
            userId: 7,
            senderName: 'Jane',
            createdAt: '2024-01-01T12:00:00+00:00'
        );

        $conversationDto = new ConversationMessageDto(
            conversationId: 42,
            messages: [$messageDto]
        );

        $this->service
            ->shouldReceive('openConversation')
            ->once()
            ->with(7, 100) // userId, bookId
            ->andReturn($conversationDto);

        $response = ($this->controller)(100);

        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode((string) $response->getContent(), true);

        $this->assertArrayHasKey('conversation_id', $data);
        $this->assertArrayHasKey('messages', $data);

        $this->assertSame(42, $data['conversation_id']);
        $this->assertCount(1, $data['messages']);
        $this->assertSame('I have a question about this book', $data['messages'][0]['body']);
    }
}
