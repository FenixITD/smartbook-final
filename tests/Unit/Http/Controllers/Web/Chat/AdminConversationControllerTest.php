<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Chat;

use App\Dto\Chat\ConversationSummaryDto;
use App\Http\Controllers\Web\Chat\AdminConversationController;
use App\Repositories\Interfaces\ConversationRepositoryInterface;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class AdminConversationControllerTest extends TestCase
{
    private MockInterface&ConversationRepositoryInterface $repository;
    private AdminConversationController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(ConversationRepositoryInterface::class);
        $this->app->instance(ConversationRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(AdminConversationController::class);
    }

    public function test_returns_view(): void
    {
        $this->repository
            ->shouldReceive('getAllWithUnreadCounts')
            ->once()
            ->andReturn([]);

        $response = ($this->controller)();

        $this->assertInstanceOf(View::class, $response);
    }

    public function test_returns_correct_view_name(): void
    {
        $this->repository
            ->shouldReceive('getAllWithUnreadCounts')
            ->andReturn([]);

        $response = ($this->controller)();

        $this->assertSame('chat.admin', $response->name());
    }

    public function test_passes_conversations_to_view(): void
    {
        $summaryDto = new ConversationSummaryDto(
            id: 1,
            userId: 5,
            userName: 'John Doe',
            userEmail: 'john@example.com',
            bookId: 10,
            bookTitle: 'Dune',
            status: 'open',
            lastMessageBody: 'Hello',
            updatedAt: '01.01.2024 10:00',
            unreadCount: 2
        );

        $this->repository
            ->shouldReceive('getAllWithUnreadCounts')
            ->andReturn([$summaryDto]);

        $response = ($this->controller)();
        $data = $response->getData();

        $this->assertArrayHasKey('conversations', $data);
        $this->assertCount(1, $data['conversations']);
        $this->assertSame($summaryDto, $data['conversations'][0]);
    }
}
