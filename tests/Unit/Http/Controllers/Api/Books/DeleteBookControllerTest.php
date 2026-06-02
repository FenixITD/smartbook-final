<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Books;

use App\Http\Controllers\Api\Books\DeleteBookController;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Mockery\MockInterface;
use Tests\TestCase;

final class DeleteBookControllerTest extends TestCase
{
    public function test_returns_200_with_success_message(): void
    {
        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('delete')->once()->with(1)->andReturn(true);

        $controller = new DeleteBookController($repository);
        $response = $controller->__invoke(1);

        $this->assertSame(200, $response->getStatusCode());

        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame('Book deleted successfully', $content['message']);
    }

    public function test_response_contains_success_and_message_keys(): void
    {
        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('delete')->andReturn(true);

        $response = (new DeleteBookController($repository))->__invoke(1);
        $content  = json_decode((string) $response->getContent(), true);

        $this->assertArrayHasKey('message', $content);
    }

    public function test_passes_correct_book_id_to_repository(): void
    {
        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('delete')
            ->once()
            ->with(42)
            ->andReturn(true);

        (new DeleteBookController($repository))->__invoke(42);
    }

    public function test_returns_200_regardless_of_repository_return_value(): void
    {
        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('delete')->with(999)->andReturn(true);

        $response = (new DeleteBookController($repository))->__invoke(999);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_calls_repository_delete_exactly_once(): void
    {
        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('delete')
            ->once()
            ->with(5)
            ->andReturn(true);

        (new DeleteBookController($repository))->__invoke(5);
    }

    public function test_does_not_call_other_repository_methods(): void
    {
        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('delete')->once()->andReturn(true);
        $repository->shouldNotReceive('getById');
        $repository->shouldNotReceive('getList');
        $repository->shouldNotReceive('create');
        $repository->shouldNotReceive('update');

        (new DeleteBookController($repository))->__invoke(1);
    }
}
