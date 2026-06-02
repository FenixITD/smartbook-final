<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Books;

use App\Http\Controllers\Api\Books\GetBookController;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetBookControllerTest extends TestCase
{
    use BookResponseDtoFactory;

    public function test_returns_200_with_book_data_when_book_exists(): void
    {
        $book = $this->makeBookResponseDto(id: 42, title: 'Clean Code');

        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('getById')
            ->once()
            ->with(42)
            ->andReturn($book);

        $controller = new GetBookController($repository);
        $response = $controller->__invoke(42);

        $this->assertSame(200, $response->getStatusCode());

        $content = json_decode((string) $response->getContent(), true);

        $this->assertSame(42, $content['data']['id']);
        $this->assertSame('Clean Code', $content['data']['title']);
        $this->assertSame('test-book', $content['data']['slug']);
        $this->assertSame(1, $content['data']['authorId']);
        $this->assertSame(19.99, $content['data']['price']);
        $this->assertSame(10, $content['data']['stock']);
        $this->assertSame('active', $content['data']['status']);
    }

    public function test_response_contains_all_expected_keys(): void
    {
        $book = $this->makeBookResponseDto();

        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('getById')->andReturn($book);

        $controller = new GetBookController($repository);
        $response = $controller->__invoke(1);

        $data = json_decode((string) $response->getContent(), true)['data'];

        foreach (['id', 'title', 'slug', 'authorId', 'description', 'price', 'stock', 'status', 'createdAt', 'updatedAt'] as $key) {
            $this->assertArrayHasKey($key, $data, "Key '{$key}' missing in response");
        }
    }

    public function test_calls_repository_exactly_once_with_given_id(): void
    {
        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('getById')
            ->once()
            ->with(7)
            ->andReturn($this->makeBookResponseDto(id: 7));

        (new GetBookController($repository))->__invoke(7);
    }

    public function test_does_not_call_other_repository_methods(): void
    {
        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('getById')->once()->andReturn($this->makeBookResponseDto());
        $repository->shouldNotReceive('getList');
        $repository->shouldNotReceive('create');
        $repository->shouldNotReceive('update');
        $repository->shouldNotReceive('delete');

        (new GetBookController($repository))->__invoke(1);
    }
}
