<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Books;

use App\Dto\Book\BookDto;
use App\Http\Controllers\Api\Books\CreateBookController;
use App\Http\Requests\Book\BookDataRequest;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Tests\TestCase;

final class CreateBookControllerTest extends TestCase
{
    use BookResponseDtoFactory;

    public function test_returns_201_with_created_book(): void
    {
        $created = $this->makeBookResponseDto(id: 10, title: 'New Book', status: 'draft');

        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('create')->once()->andReturn($created);

        $request = $this->makeBookDataRequest();
        $controller = new CreateBookController($repository);
        $response = $controller->__invoke($request);

        $this->assertSame(201, $response->getStatusCode());

        $content = json_decode((string) $response->getContent(), true);
        $this->assertSame(10, $content['data']['id']);
        $this->assertSame('New Book', $content['data']['title']);
        $this->assertSame('draft', $content['data']['status']);
    }

    public function test_response_contains_all_expected_keys(): void
    {
        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('create')->andReturn($this->makeBookResponseDto());

        $response = (new CreateBookController($repository))->__invoke($this->makeBookDataRequest());
        $data = json_decode((string) $response->getContent(), true)['data'];

        foreach (['id', 'title', 'slug', 'authorId', 'description', 'price', 'stock', 'status', 'createdAt', 'updatedAt'] as $key) {
            $this->assertArrayHasKey($key, $data, "Key '{$key}' missing in response");
        }
    }

    public function test_passes_correct_dto_to_repository(): void
    {
        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('create')
            ->once()
            ->withArgs(function (BookDto $dto): bool {
                return $dto->title === 'Domain-Driven Design'
                    && $dto->slug === 'domain-driven-design'
                    && $dto->authorId === 3
                    && $dto->price === '29.99'
                    && $dto->stock === 5
                    && $dto->status === 'active';
            })
            ->andReturn($this->makeBookResponseDto());

        $request = $this->makeBookDataRequest([
            'title' => 'Domain-Driven Design',
            'slug' => 'domain-driven-design',
            'authorId' => 3,
            'price' => 29.99,
            'stock' => 5,
            'status' => 'active',
        ]);

        (new CreateBookController($repository))->__invoke($request);
    }

    public function test_passes_optional_nullable_fields_to_dto(): void
    {
        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('create')
            ->once()
            ->withArgs(function (BookDto $dto): bool {
                return $dto->publishYear === 2021;
            })
            ->andReturn($this->makeBookResponseDto());

        $request = $this->makeBookDataRequest([
            'publishYear' => 2021,
        ]);

        (new CreateBookController($repository))->__invoke($request);
    }

    public function test_calls_repository_create_exactly_once(): void
    {
        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('create')
            ->once()
            ->andReturn($this->makeBookResponseDto());

        (new CreateBookController($repository))->__invoke($this->makeBookDataRequest());
    }

    public function test_does_not_call_other_repository_methods(): void
    {
        /** @var BookRepositoryInterface&MockInterface $repository */
        $repository = $this->mock(BookRepositoryInterface::class);
        $repository->shouldReceive('create')->once()->andReturn($this->makeBookResponseDto());
        $repository->shouldNotReceive('getById');
        $repository->shouldNotReceive('getList');
        $repository->shouldNotReceive('update');
        $repository->shouldNotReceive('delete');

        (new CreateBookController($repository))->__invoke($this->makeBookDataRequest());
    }

    private function makeBookDataRequest(array $overrides = []): BookDataRequest
    {
        $data = array_merge($this->validBookRequestData(), $overrides);

        /** @var BookDataRequest $request */
        $request = BookDataRequest::createFrom(
            Request::create('/api/books', 'POST', $data)
        );

        return $request;
    }
}
