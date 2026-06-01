<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Chat;

use App\Http\Controllers\Web\Chat\CloseConversationController;
use App\Repositories\Interfaces\ConversationRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class CloseConversationControllerTest extends TestCase
{
    private MockInterface&ConversationRepositoryInterface $repository;
    private CloseConversationController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(ConversationRepositoryInterface::class);
        $this->app->instance(ConversationRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(CloseConversationController::class);
    }

    public function test_calls_close_on_repository_and_returns_json(): void
    {
        $this->repository
            ->shouldReceive('close')
            ->once()
            ->with(42);

        $response = ($this->controller)(42);

        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode((string) $response->getContent(), true);
        $this->assertArrayHasKey('status', $data);
        $this->assertSame('closed', $data['status']);
    }
}
