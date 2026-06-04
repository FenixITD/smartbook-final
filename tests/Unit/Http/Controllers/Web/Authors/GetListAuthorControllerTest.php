<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Authors;

use App\Dto\Author\AuthorFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Http\Controllers\Web\Authors\GetListAuthorController;
use App\Http\Requests\Author\AuthorListRequest;
use App\Services\Author\FetchWebListAuthorService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetListAuthorControllerTest extends TestCase
{
    private MockInterface&FetchWebListAuthorService $service;
    private GetListAuthorController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = Mockery::mock(FetchWebListAuthorService::class);
        $this->app->instance(FetchWebListAuthorService::class, $this->service);
        $this->controller = $this->app->make(GetListAuthorController::class);
    }

    public function test_returns_view(): void
    {
        $this->service
            ->shouldReceive('get')
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        $response = ($this->controller)($this->makeRequest());

        $this->assertInstanceOf(View::class, $response);
    }

    public function test_returns_correct_view_name(): void
    {
        $this->service
            ->shouldReceive('get')
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        $response = ($this->controller)($this->makeRequest());

        $this->assertSame('authors.list', $response->name());
    }

    public function test_view_contains_paginated_key(): void
    {
        $this->service
            ->shouldReceive('get')
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        $response = ($this->controller)($this->makeRequest());

        $this->assertArrayHasKey('paginated', $response->getData());
    }

    public function test_view_contains_paginated_data_from_service(): void
    {
        $paginated = Mockery::mock(PaginatedResponseDto::class);

        $this->service
            ->shouldReceive('get')
            ->andReturn($paginated);

        $response = ($this->controller)($this->makeRequest());

        $this->assertSame($paginated, $response->getData()['paginated']);
    }

    public function test_calls_service_once(): void
    {
        $this->service
            ->shouldReceive('get')
            ->once()
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        ($this->controller)($this->makeRequest());
    }

    public function test_passes_filters_dto_from_request_to_service(): void
    {
        $this->service
            ->shouldReceive('get')
            ->once()
            ->with(Mockery::on(function (AuthorFiltersDto $dto) {
                return $dto->search === 'tolkien'
                    && $dto->perPage === 10
                    && $dto->sortBy === 'name'
                    && $dto->sortDirection === 'desc';
            }))
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        ($this->controller)($this->makeRequest([
            'search' => 'tolkien',
            'perPage' => 10,
            'sortBy' => 'name',
            'sortDirection' => 'desc',
        ]));
    }

    public function test_uses_default_filters_when_no_query_params(): void
    {
        $this->service
            ->shouldReceive('get')
            ->once()
            ->with(Mockery::on(function (AuthorFiltersDto $dto) {
                return $dto->search === null
                    && $dto->perPage === 15
                    && $dto->sortBy === 'id'
                    && $dto->sortDirection === 'asc';
            }))
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        ($this->controller)($this->makeRequest());
    }

    private function makeRequest(array $params = []): AuthorListRequest
    {
        return AuthorListRequest::createFrom(
            Request::create('/authors', 'GET', $params)
        );
    }
}
