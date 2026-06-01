<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Orders;

use App\Dto\Order\OrderFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Http\Controllers\Web\Orders\GetListOrderController;
use App\Http\Requests\Order\OrderListRequest;
use App\Services\Order\GetWebListOrderService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetListOrderControllerTest extends TestCase
{
    private MockInterface&GetWebListOrderService $service;
    private GetListOrderController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = Mockery::mock(GetWebListOrderService::class);
        $this->app->instance(GetWebListOrderService::class, $this->service);
        $this->controller = $this->app->make(GetListOrderController::class);
    }

    public function test_returns_view_with_paginated_data(): void
    {
        $paginated = Mockery::mock(PaginatedResponseDto::class);

        $this->service
            ->shouldReceive('get')
            ->once()
            ->with(Mockery::on(function (OrderFiltersDto $dto) {
                return $dto->search === 'pending'
                    && $dto->perPage === 20;
            }))
            ->andReturn($paginated);

        $response = ($this->controller)($this->makeRequest(['search' => 'pending', 'perPage' => 20]));

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('orders.list', $response->name());
        $this->assertArrayHasKey('paginated', $response->getData());
        $this->assertSame($paginated, $response->getData()['paginated']);
    }

    private function makeRequest(array $params = []): OrderListRequest
    {
        return OrderListRequest::createFrom(
            Request::create('/orders', 'GET', $params)
        );
    }
}
