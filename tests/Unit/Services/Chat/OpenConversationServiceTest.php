<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Chat;

use App\Dto\Chat\ConversationMessageDto;
use App\Dto\Chat\MessageDto;
use App\Repositories\Interfaces\ConversationRepositoryInterface;
use App\Services\Chat\OpenConversationService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class OpenConversationServiceTest extends TestCase
{
    private ConversationRepositoryInterface&MockInterface $conversationRepository;
    private OpenConversationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conversationRepository = Mockery::mock(ConversationRepositoryInterface::class);
        $this->service = new OpenConversationService($this->conversationRepository);
    }

    public function test_open_conversation_returns_dto(): void
    {
        $message = new MessageDto(1, 10, 'body', 2, 'Name', 'date');
        $this->conversationRepository->expects('findOrCreateByUserAndBook')->with(1, 2)->andReturn(10);
        $this->conversationRepository->expects('getMessages')->with(10)->andReturn([$message]);
        $this->conversationRepository->expects('getStatus')->with(10)->andReturn('open');

        $result = $this->service->openConversation(1, 2);

        $this->assertInstanceOf(ConversationMessageDto::class, $result);
        $this->assertSame(10, $result->conversationId);
        $this->assertSame('open', $result->status);
        $this->assertCount(1, $result->messages);
        $this->assertSame($message, $result->messages[0]);
    }

    public function test_open_conversation_exposes_closed_status(): void
    {
        $this->conversationRepository->expects('findOrCreateByUserAndBook')->with(1, 2)->andReturn(10);
        $this->conversationRepository->expects('getMessages')->with(10)->andReturn([]);
        $this->conversationRepository->expects('getStatus')->with(10)->andReturn('closed');

        $result = $this->service->openConversation(1, 2);

        $this->assertSame('closed', $result->status);
    }
}
