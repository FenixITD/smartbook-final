<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Genres;

use App\Dto\Genre\GenreFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Http\Controllers\Web\Genres\GetListGenreController;
use App\Http\Requests\Genre\GenreListRequest;
use App\Services\Genre\GetWebListGenreService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetListGenreControllerTest extends TestCase
{
    private MockInterface&GetWebListGenreService $service;
    private GetListGenreController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = Mockery::mock(GetWebListGenreService::class);
        $this->app->instance(GetWebListGenreService::class, $this->service);
        $this->controller = $this->app->make(GetListGenreController::class);
    }

    public function test_returns_view_with_paginated_data(): void
    {
        $paginated = Mockery::mock(PaginatedResponseDto::class);

        $this->service
            ->shouldReceive('get')
            ->once()
            ->with(Mockery::on(function (GenreFiltersDto $dto) {
                return $dto->search === 'sci-fi'
                    && $dto->perPage === 20;
            }))
            ->andReturn($paginated);

        $response = ($this->controller)($this->makeRequest(['search' => 'sci-fi', 'perPage' => 20]));

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('genres.list', $response->name());
        $this->assertArrayHasKey('paginated', $response->getData());
        $this->assertSame($paginated, $response->getData()['paginated']);
    }

    private function makeRequest(array $params = []): GenreListRequest
    {
        return GenreListRequest::createFrom(
            Request::create('/genres', 'GET', $params)
        );
    }
}
