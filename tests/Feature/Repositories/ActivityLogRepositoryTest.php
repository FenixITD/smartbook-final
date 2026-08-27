<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Dto\ActivityLog\ActivityLogFiltersDto;
use App\Dto\ActivityLog\ActivityLogResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Eloquent\ActivityLogRepository;
use App\Services\Clickhouse\ClickhouseManagerService;
use App\Services\Clickhouse\ClickhouseQueryBuilderService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class ActivityLogRepositoryTest extends TestCase
{
    private ClickhouseManagerService&MockInterface $clickhouse;
    private ClickhouseQueryBuilderService&MockInterface $builder;
    private ActivityLogRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $activityLogger = Mockery::mock(\Spatie\Activitylog\ActivityLogger::class);
        $activityLogger->shouldReceive('useLog')->andReturnSelf();
        $activityLogger->shouldReceive('event')->andReturnSelf();
        $activityLogger->shouldReceive('performedOn')->andReturnSelf();
        $activityLogger->shouldReceive('withProperties')->andReturnSelf();
        $activityLogger->shouldReceive('log')->andReturnNull();
        $this->app->singleton(\Spatie\Activitylog\ActivityLogger::class, fn () => $activityLogger);

        $this->clickhouse = Mockery::mock(ClickhouseManagerService::class);
        $this->builder = Mockery::mock(ClickhouseQueryBuilderService::class);
        $this->repository = new ActivityLogRepository($this->clickhouse);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function expectTableCall(): void
    {
        $this->clickhouse
            ->shouldReceive('table')
            ->once()
            ->with('activity_log')
            ->andReturn($this->builder);

        $this->builder
            ->shouldReceive('final')
            ->once()
            ->andReturn($this->builder);
    }

    private function expectDefaultTerminalCalls(int $total, array $rows): void
    {
        $this->builder
            ->shouldReceive('count')
            ->once()
            ->andReturn($total);

        $this->builder
            ->shouldReceive('orderByDesc')
            ->once()
            ->with('created_at')
            ->andReturn($this->builder);

        $this->builder
            ->shouldReceive('limit')
            ->once()
            ->andReturn($this->builder);

        $this->builder
            ->shouldReceive('offset')
            ->once()
            ->andReturn($this->builder);

        $this->builder
            ->shouldReceive('get')
            ->once()
            ->andReturn($rows);
    }

    public function test_returns_paginated_response_dto(): void
    {
        $this->expectTableCall();
        $this->expectDefaultTerminalCalls(total: 0, rows: []);

        $result = $this->repository->getPaginated(new ActivityLogFiltersDto());

        $this->assertInstanceOf(PaginatedResponseDto::class, $result);
    }

    public function test_no_filters_applied_when_all_are_null_or_empty(): void
    {
        $this->expectTableCall();

        $this->builder->shouldNotReceive('where');
        $this->builder->shouldNotReceive('whereIn');

        $this->expectDefaultTerminalCalls(total: 0, rows: []);

        $this->repository->getPaginated(new ActivityLogFiltersDto());

        $this->addToAssertionCount(1);
    }

    public function test_applies_log_name_filter(): void
    {
        $this->expectTableCall();

        $this->builder
            ->shouldReceive('where')
            ->once()
            ->with('log_name', 'audit')
            ->andReturn($this->builder);

        $this->expectDefaultTerminalCalls(total: 0, rows: []);

        $this->repository->getPaginated(new ActivityLogFiltersDto(logName: 'audit'));

        $this->addToAssertionCount(1);
    }

    public function test_applies_causer_id_filter(): void
    {
        $this->expectTableCall();

        $this->builder
            ->shouldReceive('where')
            ->once()
            ->with('causer_id', 42)
            ->andReturn($this->builder);

        $this->expectDefaultTerminalCalls(total: 0, rows: []);

        $this->repository->getPaginated(new ActivityLogFiltersDto(causerId: 42));

        $this->addToAssertionCount(1);
    }

    public function test_applies_subject_type_filter(): void
    {
        $this->expectTableCall();

        $this->builder
            ->shouldReceive('where')
            ->once()
            ->with('subject_type', 'App\\Models\\User')
            ->andReturn($this->builder);

        $this->expectDefaultTerminalCalls(total: 0, rows: []);

        $this->repository->getPaginated(new ActivityLogFiltersDto(subjectType: 'App\\Models\\User'));

        $this->addToAssertionCount(1);
    }

    public function test_applies_log_names_filter(): void
    {
        $this->expectTableCall();

        $this->builder
            ->shouldReceive('whereIn')
            ->once()
            ->with('log_name', ['audit', 'system'])
            ->andReturn($this->builder);

        $this->expectDefaultTerminalCalls(total: 0, rows: []);

        $this->repository->getPaginated(new ActivityLogFiltersDto(logNames: ['audit', 'system']));

        $this->addToAssertionCount(1);
    }

    public function test_applies_all_filters_together(): void
    {
        $this->expectTableCall();

        $this->builder
            ->shouldReceive('where')
            ->once()
            ->with('log_name', 'audit')
            ->andReturn($this->builder);

        $this->builder
            ->shouldReceive('where')
            ->once()
            ->with('causer_id', 7)
            ->andReturn($this->builder);

        $this->builder
            ->shouldReceive('where')
            ->once()
            ->with('subject_type', 'App\\Models\\Order')
            ->andReturn($this->builder);

        $this->builder
            ->shouldReceive('whereIn')
            ->once()
            ->with('log_name', ['audit', 'debug'])
            ->andReturn($this->builder);

        $this->expectDefaultTerminalCalls(total: 0, rows: []);

        $this->repository->getPaginated(new ActivityLogFiltersDto(
            logName: 'audit',
            subjectType: 'App\\Models\\Order',
            causerId: 7,
            logNames: ['audit', 'debug'],
        ));

        $this->addToAssertionCount(1);
    }

    public function test_does_not_apply_log_names_filter_when_empty(): void
    {
        $this->expectTableCall();

        $this->builder->shouldNotReceive('whereIn');

        $this->expectDefaultTerminalCalls(total: 0, rows: []);

        $this->repository->getPaginated(new ActivityLogFiltersDto(logNames: []));

        $this->addToAssertionCount(1);
    }

    public function test_passes_correct_limit_based_on_per_page(): void
    {
        $this->expectTableCall();

        $this->builder->shouldReceive('count')->once()->andReturn(0);
        $this->builder->shouldReceive('orderByDesc')->once()->andReturn($this->builder);

        $this->builder
            ->shouldReceive('limit')
            ->once()
            ->with(50)
            ->andReturn($this->builder);

        $this->builder->shouldReceive('offset')->once()->andReturn($this->builder);
        $this->builder->shouldReceive('get')->once()->andReturn([]);

        $this->repository->getPaginated(new ActivityLogFiltersDto(page: 1, perPage: 50));

        $this->addToAssertionCount(1);
    }

    public function test_passes_correct_offset_for_first_page(): void
    {
        $this->expectTableCall();

        $this->builder->shouldReceive('count')->once()->andReturn(0);
        $this->builder->shouldReceive('orderByDesc')->once()->andReturn($this->builder);
        $this->builder->shouldReceive('limit')->once()->andReturn($this->builder);

        $this->builder
            ->shouldReceive('offset')
            ->once()
            ->with(0)
            ->andReturn($this->builder);

        $this->builder->shouldReceive('get')->once()->andReturn([]);

        $this->repository->getPaginated(new ActivityLogFiltersDto(page: 1, perPage: 20));

        $this->addToAssertionCount(1);
    }

    public function test_passes_correct_offset_for_subsequent_page(): void
    {
        $this->expectTableCall();

        $this->builder->shouldReceive('count')->once()->andReturn(0);
        $this->builder->shouldReceive('orderByDesc')->once()->andReturn($this->builder);
        $this->builder->shouldReceive('limit')->once()->andReturn($this->builder);

        $this->builder
            ->shouldReceive('offset')
            ->once()
            ->with(40)
            ->andReturn($this->builder);

        $this->builder->shouldReceive('get')->once()->andReturn([]);

        $this->repository->getPaginated(new ActivityLogFiltersDto(page: 3, perPage: 20));

        $this->addToAssertionCount(1);
    }

    public function test_result_contains_rows_returned_by_builder(): void
    {
        $rows = [
            ['id' => 1, 'log_name' => 'audit', 'created_at' => '2024-01-01 00:00:00'],
            ['id' => 2, 'log_name' => 'audit', 'created_at' => '2024-01-02 00:00:00'],
        ];

        $this->expectTableCall();
        $this->expectDefaultTerminalCalls(total: 2, rows: $rows);

        $result = $this->repository->getPaginated(new ActivityLogFiltersDto());

        $this->assertCount(2, $result->items);
        $this->assertContainsOnlyInstancesOf(ActivityLogResponseDto::class, $result->items);
        $this->assertSame(1, $result->items[0]->id);
        $this->assertSame('audit', $result->items[0]->logName);
        $this->assertSame('2024-01-01 00:00:00', $result->items[0]->createdAt);
        $this->assertSame(2, $result->items[1]->id);
        $this->assertSame('audit', $result->items[1]->logName);
        $this->assertSame('2024-01-02 00:00:00', $result->items[1]->createdAt);
    }

    public function test_result_contains_total_from_count(): void
    {
        $this->expectTableCall();
        $this->expectDefaultTerminalCalls(total: 99, rows: []);

        $result = $this->repository->getPaginated(new ActivityLogFiltersDto());

        $this->assertSame(99, $result->total);
    }

    public function test_result_contains_correct_pagination_metadata(): void
    {
        $this->expectTableCall();
        $this->expectDefaultTerminalCalls(total: 100, rows: []);

        $result = $this->repository->getPaginated(new ActivityLogFiltersDto(page: 2, perPage: 25));

        $this->assertSame(2, $result->currentPage);
        $this->assertSame(25, $result->perPage);
        $this->assertSame(4, $result->lastPage);
    }
}
