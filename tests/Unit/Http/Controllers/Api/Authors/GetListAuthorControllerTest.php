<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Authors;

use App\Dto\Author\AuthorFiltersDto;
use App\Dto\Author\AuthorResponseDto;
use App\Http\Controllers\Api\Authors\GetListAuthorController;
use App\Http\Requests\Author\AuthorListRequest;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetListAuthorControllerTest extends TestCase
{
    private MockInterface&AuthorRepositoryInterface $repository;
    private GetListAuthorController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(AuthorRepositoryInterface::class);
        $this->app->instance(AuthorRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(GetListAuthorController::class);
    }

    public function test_returns_200_with_authors_list(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->once()
            ->andReturn([
                $this->makeResponseDto(1, 'Author One'),
                $this->makeResponseDto(2, 'Author Two'),
            ]);

        $response = ($this->controller)($this->makeRequest());

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_all_authors(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->andReturn([
                $this->makeResponseDto(1, 'Author One'),
                $this->makeResponseDto(2, 'Author Two'),
                $this->makeResponseDto(3, 'Author Three'),
            ]);

        $response = ($this->controller)($this->makeRequest());
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertCount(3, $data);
        $this->assertSame(1, $data[0]['id']);
        $this->assertSame('Author One', $data[0]['name']);
        $this->assertSame(2, $data[1]['id']);
        $this->assertSame(3, $data[2]['id']);
    }

    public function test_returns_empty_data_array_when_no_authors(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->andReturn([]);

        $response = ($this->controller)($this->makeRequest());
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([], $data);
    }

    public function test_passes_filters_dto_from_request_to_repository(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->once()
            ->with(Mockery::on(function (AuthorFiltersDto $arg) {
                return $arg->search === 'tolkien'
                    && $arg->perPage === 10
                    && $arg->sortBy === 'name'
                    && $arg->sortDirection === 'desc';
            }))
            ->andReturn([]);

        ($this->controller)($this->makeRequest([
            'search' => 'tolkien',
            'perPage' => 10,
            'sortBy' => 'name',
            'sortDirection' => 'desc',
        ]));
    }

    public function test_uses_default_filters_when_no_query_params(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->once()
            ->with(Mockery::on(function (AuthorFiltersDto $arg) {
                return $arg->search === null
                    && $arg->perPage === 15
                    && $arg->sortBy === 'id'
                    && $arg->sortDirection === 'asc';
            }))
            ->andReturn([]);

        ($this->controller)($this->makeRequest());
    }

    private function makeRequest(array $params = []): AuthorListRequest
    {
        return AuthorListRequest::createFrom(
            Request::create('/api/authors', 'GET', $params)
        );
    }

    private function makeResponseDto(int $id, string $name): AuthorResponseDto
    {
        return new AuthorResponseDto(
            id: $id,
            name: $name,
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
