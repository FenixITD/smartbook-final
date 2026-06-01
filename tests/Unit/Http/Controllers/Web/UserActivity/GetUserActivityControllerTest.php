<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\UserActivity;

use App\Dto\ActivityLog\ActivityLogFiltersDto;
use App\Dto\Book\BookResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Http\Controllers\Web\UserActivity\GetUserActivityController;
use App\Http\Requests\UserActivity\UserActivityFilterRequest;
use App\Services\User\UserActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetUserActivityControllerTest extends TestCase
{
    private MockInterface&UserActivityService $service;
    private GetUserActivityController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = Mockery::mock(UserActivityService::class);
        $this->app->instance(UserActivityService::class, $this->service);
        $this->controller = $this->app->make(GetUserActivityController::class);
    }

    public function test_returns_view_with_user_activity_data(): void
    {
        Auth::shouldReceive('id')->once()->andReturn(10);

        $logs = Mockery::mock(PaginatedResponseDto::class);
        $booksById = [1 => Mockery::mock(BookResponseDto::class)];

        $this->service
            ->shouldReceive('getWithBooks')
            ->once()
            ->andReturn([$logs, $booksById]);

        $response = ($this->controller)($this->makeRequest());

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('user-activity.index', $response->name());

        $data = $response->getData();
        $this->assertArrayHasKey('logs', $data);
        $this->assertArrayHasKey('booksById', $data);
        $this->assertSame($logs, $data['logs']);
        $this->assertSame($booksById, $data['booksById']);
    }

    public function test_passes_correct_dto_to_service(): void
    {
        Auth::shouldReceive('id')->once()->andReturn(42);

        $this->service
            ->shouldReceive('getWithBooks')
            ->once()
            ->with(Mockery::on(function (ActivityLogFiltersDto $dto) {
                return $dto->perPage === 25
                    && $dto->causerId === 42
                    && $dto->logNames === ['CartItem', 'Favorite'];
            }))
            ->andReturn([Mockery::mock(PaginatedResponseDto::class), []]);

        ($this->controller)($this->makeRequest(['perPage' => 25]));
    }

    private function makeRequest(array $data = []): UserActivityFilterRequest
    {
        return UserActivityFilterRequest::createFrom(
            Request::create('/user-activity', 'GET', $data)
        );
    }
}
