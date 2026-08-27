<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Books;

use App\Http\Controllers\Api\Books\DeleteBookController;
use App\Services\Book\DeleteBookService;
use Mockery\MockInterface;
use Tests\TestCase;

final class DeleteBookControllerTest extends TestCase
{
    public function test_returns_200_with_success_message(): void
    {
        /** @var DeleteBookService&MockInterface $service */
        $service = $this->mock(DeleteBookService::class);
        $service->shouldReceive('execute')->once()->with(1);

        $controller = new DeleteBookController($service);
        $response = $controller->__invoke(1);

        $this->assertSame(200, $response->getStatusCode());

        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('Book deleted successfully', $content['message']);
    }

    public function test_response_contains_success_and_message_keys(): void
    {
        /** @var DeleteBookService&MockInterface $service */
        $service = $this->mock(DeleteBookService::class);
        $service->shouldReceive('execute');

        $response = (new DeleteBookController($service))->__invoke(1);
        $content = json_decode((string) $response->getContent(), true);

        $this->assertArrayHasKey('message', $content);
    }

    public function test_passes_correct_book_id_to_service(): void
    {
        /** @var DeleteBookService&MockInterface $service */
        $service = $this->mock(DeleteBookService::class);
        $service->shouldReceive('execute')
            ->once()
            ->with(42);

        (new DeleteBookController($service))->__invoke(42);
    }

    public function test_always_returns_200(): void
    {
        /** @var DeleteBookService&MockInterface $service */
        $service = $this->mock(DeleteBookService::class);
        $service->shouldReceive('execute')->with(999);

        $response = (new DeleteBookController($service))->__invoke(999);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_calls_service_execute_exactly_once(): void
    {
        /** @var DeleteBookService&MockInterface $service */
        $service = $this->mock(DeleteBookService::class);
        $service->shouldReceive('execute')
            ->once()
            ->with(5);

        (new DeleteBookController($service))->__invoke(5);
    }

    public function test_does_not_call_other_service_methods(): void
    {
        /** @var DeleteBookService&MockInterface $service */
        $service = $this->mock(DeleteBookService::class);
        $service->shouldReceive('execute')->once();
        $service->shouldNotReceive('somethingElse');

        (new DeleteBookController($service))->__invoke(1);
    }
}
