<?php

declare(strict_types=1);

namespace Tests\Unit\Console;

use App\Console\Commands\ClickhouseMigrateCommand;
use App\Services\Clickhouse\ClickhouseManagerService;
use Illuminate\Support\Facades\File;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class ClickhouseMigrateCommandTest extends TestCase
{
    public function test_fails_when_clickhouse_is_unavailable(): void
    {
        /** @var MockInterface&ClickhouseManagerService $manager */
        $manager = Mockery::mock(ClickhouseManagerService::class);

        $manager->shouldReceive('ping')->once()->andReturn(false);
        $manager->shouldNotReceive('execute');

        $this->app->instance(ClickhouseManagerService::class, $manager);

        $this->artisan(ClickhouseMigrateCommand::class)
            ->expectsOutput('Cannot connect to ClickHouse. Check CLICKHOUSE_* env variables.')
            ->assertExitCode(1);
    }

    public function test_returns_success_when_no_migration_files_found(): void
    {
        /** @var MockInterface&ClickhouseManagerService $manager */
        $manager = Mockery::mock(ClickhouseManagerService::class);

        $manager->shouldReceive('ping')->once()->andReturn(true);
        $manager->shouldNotReceive('execute');

        $this->app->instance(ClickhouseManagerService::class, $manager);

        File::shouldReceive('glob')
            ->once()
            ->with(database_path('clickhouse/*.sql'))
            ->andReturn([]);

        $this->artisan(ClickhouseMigrateCommand::class)
            ->expectsOutput('No migration files found in database/clickhouse/')
            ->assertExitCode(0);
    }

    public function test_executes_migration_files_and_statements_successfully(): void
    {
        /** @var MockInterface&ClickhouseManagerService $manager */
        $manager = Mockery::mock(ClickhouseManagerService::class);

        $manager->shouldReceive('ping')->once()->andReturn(true);

        $manager->shouldReceive('execute')->once()->with('CREATE TABLE fake_table');
        $manager->shouldReceive('execute')->once()->with('DROP TABLE old_table');

        $this->app->instance(ClickhouseManagerService::class, $manager);

        $fakeFilePath = database_path('clickhouse/01_create_tables.sql');

        $fakeSqlContent = "CREATE TABLE fake_table; \n DROP TABLE old_table; \n ;   ";

        File::shouldReceive('glob')
            ->once()
            ->with(database_path('clickhouse/*.sql'))
            ->andReturn([$fakeFilePath]);

        File::shouldReceive('get')
            ->once()
            ->with($fakeFilePath)
            ->andReturn($fakeSqlContent);

        $this->artisan(ClickhouseMigrateCommand::class)
            ->expectsOutput('Running: 01_create_tables.sql')
            ->expectsOutputToContain('Done')
            ->expectsOutput('ClickHouse migrations finished.')
            ->assertExitCode(0);
    }
}
