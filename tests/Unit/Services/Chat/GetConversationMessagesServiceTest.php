<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Chat;

use App\Dto\Chat\GetConversationMessagesDto;
use App\Dto\Chat\MessageDto;
use App\Repositories\Interfaces\ConversationRepositoryInterface;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use App\Services\Chat\GetConversationMessagesService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class GetConversationMessagesServiceTest extends TestCase
{
    private ConversationRepositoryInterface&MockInterface $conversationRepository;
    private MessageRepositoryInterface&MockInterface $messageRepository;
    private GetConversationMessagesService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conversationRepository = Mockery::mock(ConversationRepositoryInterface::class);
        $this->messageRepository = Mockery::mock(MessageRepositoryInterface::class);
        $this->service = new GetConversationMessagesService($this->conversationRepository, $this->messageRepository);
    }

    public function test_get_conversation_messages_for_admin(): void
    {
        $dto = new GetConversationMessagesDto(10, 1, true);
        $message = new MessageDto(1, 10, 'body', 2, 'Name', 'date');

        $this->conversationRepository->expects('getOwnerId')->never();
        $this->messageRepository->expects('markUserMessagesAsRead')->with(10);
        $this->conversationRepository->expects('getMessages')->with(10)->andReturn([$message]);

        $result = $this->service->getConversationMessages($dto);

        $this->assertSame([$message], $result);
    }

    public function test_get_conversation_messages_for_user(): void
    {
        $dto = new GetConversationMessagesDto(10, 1, false);
        $message = new MessageDto(1, 10, 'body', 2, 'Name', 'date');

        $this->conversationRepository->expects('getOwnerId')->with(10)->andReturn(1);
        $this->messageRepository->expects('markUserMessagesAsRead')->never();
        $this->conversationRepository->expects('getMessages')->with(10)->andReturn([$message]);

        $result = $this->service->getConversationMessages($dto);

        $this->assertSame([$message], $result);
    }
}
