<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Books;

use App\Http\Controllers\Web\Books\DeleteBookController;
use App\Services\Book\DeleteBookService;
use Illuminate\Http\RedirectResponse;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class DeleteBookControllerTest extends TestCase
{
    private MockInterface&DeleteBookService $service;
    private DeleteBookController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = Mockery::mock(DeleteBookService::class);
        $this->app->instance(DeleteBookService::class, $this->service);
        $this->controller = $this->app->make(DeleteBookController::class);
    }

    public function test_calls_service_execute_with_correct_id(): void
    {
        $this->service
            ->shouldReceive('execute')
            ->once()
            ->with(5);

        ($this->controller)(5);
    }

    public function test_calls_service_execute_once(): void
    {
        $this->service
            ->shouldReceive('execute')
            ->once()
            ->with(42);

        ($this->controller)(42);
    }

    public function test_returns_redirect_response(): void
    {
        $this->service->shouldReceive('execute');

        $response = ($this->controller)(1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function test_redirects_to_books_index(): void
    {
        $this->service->shouldReceive('execute');

        $response = ($this->controller)(1);

        $this->assertSame(route('books.index'), $response->getTargetUrl());
    }
}
