<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Reviews;

use App\Dto\PaginatedResponseDto;
use App\Dto\Review\ReviewFiltersDto;
use App\Http\Controllers\Web\Reviews\GetListReviewController;
use App\Http\Requests\Review\ReviewListRequest;
use App\Services\Review\GetWebListReviewService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetListReviewControllerTest extends TestCase
{
    private MockInterface&GetWebListReviewService $service;
    private GetListReviewController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = Mockery::mock(GetWebListReviewService::class);
        $this->app->instance(GetWebListReviewService::class, $this->service);
        $this->controller = $this->app->make(GetListReviewController::class);
    }

    public function test_returns_view_with_paginated_data(): void
    {
        $paginated = Mockery::mock(PaginatedResponseDto::class);

        $this->service
            ->shouldReceive('get')
            ->once()
            ->with(Mockery::on(function (ReviewFiltersDto $dto) {
                return $dto->search === 'awesome'
                    && $dto->perPage === 20;
            }))
            ->andReturn($paginated);

        $response = ($this->controller)($this->makeRequest(['search' => 'awesome', 'perPage' => 20]));

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('reviews.list', $response->name());
        $this->assertArrayHasKey('paginated', $response->getData());
        $this->assertSame($paginated, $response->getData()['paginated']);
    }

    private function makeRequest(array $params = []): ReviewListRequest
    {
        return ReviewListRequest::createFrom(
            Request::create('/reviews', 'GET', $params)
        );
    }
}
