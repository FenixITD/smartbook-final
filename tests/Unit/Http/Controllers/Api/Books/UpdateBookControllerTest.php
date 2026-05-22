<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Books;

use App\Dto\Book\BookDto;
use App\Http\Controllers\Api\Books\UpdateBookController;
use App\Http\Requests\Book\BookDataRequest;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Tests\TestCase;

final class UpdateBookControllerTest extends TestCase
{
    use BookResponseDtoFactory;

    // -------------------------------------------------------------------------
    // Happy path
    // -------------------------------------------------------------------------

    public function test_returns_200_with_updated_book_data(): void
    {
        $updated = $this->makeBookResponseDto(id: 5, title: 'Updated Title', status: 'archived');

        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('update')
            ->once()
            ->with(5, \Mockery::type(BookDto::class))
            ->andReturn($updated);

        $request    = $this->makeBookDataRequest();
        $controller = new UpdateBookController($repository);
        $response   = $controller->__invoke($request, 5);

        $this->assertSame(200, $response->getStatusCode());

        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame(5, $content['data']['id']);
        $this->assertSame('Updated Title', $content['data']['title']);
        $this->assertSame('archived', $content['data']['status']);
    }

    public function test_response_contains_all_expected_keys(): void
    {
        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('update')->andReturn($this->makeBookResponseDto());

        $response = (new UpdateBookController($repository))->__invoke($this->makeBookDataRequest(), 1);
        $data     = json_decode((string) $response->getContent(), true)['data'];

        foreach (['id', 'title', 'slug', 'authorId', 'description', 'price', 'stock', 'status', 'createdAt', 'updatedAt'] as $key) {
            $this->assertArrayHasKey($key, $data, "Key '{$key}' missing in response");
        }
    }

    // -------------------------------------------------------------------------
    // DTO mapping
    // -------------------------------------------------------------------------

    public function test_passes_correct_book_id_to_repository(): void
    {
        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('update')
            ->once()
            ->with(99, \Mockery::type(BookDto::class))
            ->andReturn($this->makeBookResponseDto(id: 99));

        (new UpdateBookController($repository))->__invoke($this->makeBookDataRequest(), 99);
    }

    public function test_passes_correct_dto_fields_to_repository(): void
    {
        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('update')
            ->once()
            ->withArgs(function (int $id, BookDto $dto): bool {
                return $id === 7
                    && $dto->title === 'Refactoring'
                    && $dto->slug === 'refactoring'
                    && $dto->price === 24.99
                    && $dto->stock === 15
                    && $dto->status === 'active';
            })
            ->andReturn($this->makeBookResponseDto());

        $request = $this->makeBookDataRequest([
            'title'  => 'Refactoring',
            'slug'   => 'refactoring',
            'price'  => 24.99,
            'stock'  => 15,
            'status' => 'active',
        ]);

        (new UpdateBookController($repository))->__invoke($request, 7);
    }

    // -------------------------------------------------------------------------
    // Repository interaction
    // -------------------------------------------------------------------------

    public function test_calls_repository_update_exactly_once(): void
    {
        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('update')
            ->once()
            ->andReturn($this->makeBookResponseDto());

        (new UpdateBookController($repository))->__invoke($this->makeBookDataRequest(), 1);
    }

    public function test_does_not_call_other_repository_methods(): void
    {
        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('update')->once()->andReturn($this->makeBookResponseDto());
        $repository->shouldNotReceive('getById');
        $repository->shouldNotReceive('getList');
        $repository->shouldNotReceive('create');
        $repository->shouldNotReceive('delete');

        (new UpdateBookController($repository))->__invoke($this->makeBookDataRequest(), 1);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeBookDataRequest(array $overrides = []): BookDataRequest
    {
        $data = array_merge($this->validBookRequestData(), $overrides);

        /** @var BookDataRequest $request */
        $request = BookDataRequest::createFrom(
            Request::create('/api/books/1', 'PUT', $data)
        );

        return $request;
    }
}
