<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\ActivityLog;

use App\Dto\ActivityLog\ActivityLogFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Http\Controllers\Web\ActivityLog\GetActivityLogController;
use App\Http\Requests\ActivityLog\ActivityLogFilterRequest;
use App\Models\Book;
use App\Repositories\Interfaces\ActivityLogRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetActivityLogControllerTest extends TestCase
{
    private MockInterface&ActivityLogRepositoryInterface $repository;
    private GetActivityLogController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(ActivityLogRepositoryInterface::class);
        $this->app->instance(ActivityLogRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(GetActivityLogController::class);
    }

    public function test_returns_activity_logs_view(): void
    {
        $this->repository
            ->shouldReceive('getPaginated')
            ->once()
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        $response = ($this->controller)($this->makeRequest());

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('activity-logs.admin', $response->getName());
    }

    public function test_view_receives_logs_from_repository(): void
    {
        $logs = Mockery::mock(PaginatedResponseDto::class);

        $this->repository
            ->shouldReceive('getPaginated')
            ->once()
            ->andReturn($logs);

        $response = ($this->controller)($this->makeRequest());

        $this->assertSame($logs, $response->getData()['logs']);
    }

    public function test_view_receives_subject_types(): void
    {
        $this->repository
            ->shouldReceive('getPaginated')
            ->once()
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        $response = ($this->controller)($this->makeRequest());

        $this->assertSame(
            array_keys(ActivityLogFilterRequest::SUBJECT_TYPE_MAP),
            $response->getData()['subjectTypes']
        );
    }

    public function test_passes_dto_to_repository(): void
    {
        $this->repository
            ->shouldReceive('getPaginated')
            ->once()
            ->with(Mockery::type(ActivityLogFiltersDto::class))
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        ($this->controller)($this->makeRequest());
    }

    public function test_uses_default_per_page_when_not_provided(): void
    {
        $this->repository
            ->shouldReceive('getPaginated')
            ->once()
            ->with(Mockery::on(fn (ActivityLogFiltersDto $dto) => $dto->perPage === 20))
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        ($this->controller)($this->makeRequest());
    }

    public function test_passes_per_page_filter_to_repository(): void
    {
        $this->repository
            ->shouldReceive('getPaginated')
            ->once()
            ->with(Mockery::on(fn (ActivityLogFiltersDto $dto) => $dto->perPage === 50))
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        ($this->controller)($this->makeRequest(['perPage' => 50]));
    }

    public function test_passes_subject_type_class_to_repository(): void
    {
        $this->repository
            ->shouldReceive('getPaginated')
            ->once()
            ->with(Mockery::on(fn (ActivityLogFiltersDto $dto) => $dto->subjectType === Book::class))
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        ($this->controller)($this->makeRequest(['subjectType' => 'Book']));
    }

    public function test_passes_null_subject_type_when_not_provided(): void
    {
        $this->repository
            ->shouldReceive('getPaginated')
            ->once()
            ->with(Mockery::on(fn (ActivityLogFiltersDto $dto) => $dto->subjectType === null))
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        ($this->controller)($this->makeRequest());
    }

    private function makeRequest(array $data = []): ActivityLogFilterRequest
    {
        $request = ActivityLogFilterRequest::createFrom(
            Request::create('/activity-logs', 'GET', $data)
        );

        $session = Mockery::mock(Store::class)->shouldIgnoreMissing();
        $request->setLaravelSession($session);

        return $request;
    }
}
