<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Chat;

use App\Dto\Chat\MessageDto;
use App\Dto\Chat\SendMessageDto;
use App\Events\MessageSentEvent;
use App\Repositories\Interfaces\ConversationRepositoryInterface;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use App\Services\Chat\SendMessageService;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class SendMessageServiceTest extends TestCase
{
    private ConversationRepositoryInterface&MockInterface $conversationRepository;
    private MessageRepositoryInterface&MockInterface $messageRepository;
    private SendMessageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conversationRepository = Mockery::mock(ConversationRepositoryInterface::class);
        $this->messageRepository = Mockery::mock(MessageRepositoryInterface::class);
        $this->service = new SendMessageService($this->conversationRepository, $this->messageRepository);
        Event::fake();
    }

    public function test_send_message_as_admin(): void
    {
        $dto = new SendMessageDto(10, 1, true, 'body');
        $message = new MessageDto(1, 10, 'body', 1, 'Admin', 'date');

        $this->conversationRepository->expects('getOwnerId')->never();
        $this->conversationRepository->expects('getStatus')->with(10)->andReturn('open');
        $this->conversationRepository->expects('getMessageCount')->with(10)->andReturn(2);
        $this->messageRepository->expects('create')->with(10, 1, 'body')->andReturn($message);

        $result = $this->service->sendMessage($dto);

        $this->assertSame($message, $result);
        Event::assertDispatched(MessageSentEvent::class, function (MessageSentEvent $event) use ($message) {
            return $event->message === $message && $event->conversationId === 10;
        });
    }

    public function test_send_message_as_user(): void
    {
        $dto = new SendMessageDto(10, 2, false, 'body');
        $message = new MessageDto(1, 10, 'body', 2, 'User', 'date');

        $this->conversationRepository->expects('getOwnerId')->with(10)->andReturn(2);
        $this->conversationRepository->expects('getStatus')->with(10)->andReturn('open');
        $this->conversationRepository->expects('getMessageCount')->with(10)->andReturn(2);
        $this->messageRepository->expects('create')->with(10, 2, 'body')->andReturn($message);

        $result = $this->service->sendMessage($dto);

        $this->assertSame($message, $result);
        Event::assertDispatched(MessageSentEvent::class, function (MessageSentEvent $event) use ($message) {
            return $event->message === $message && $event->conversationId === 10;
        });
    }
}
