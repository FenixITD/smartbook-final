<?php

declare(strict_types=1);

namespace Tests\Unit\Console;

use App\Console\Commands\ClickhouseMigrateCommand;
use App\Services\Clickhouse\ClickhouseManagerService;
use App\Services\Clickhouse\ClickhouseMigratorService;
use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

final class ClickhouseMigrateCommandTest extends TestCase
{
    public function test_fails_when_clickhouse_is_unavailable(): void
    {
        /** @var MockInterface&ClickhouseManagerService $manager */
        $manager = Mockery::mock(ClickhouseManagerService::class);

        $manager->shouldReceive('ping')->once()->andReturn(false);

        $this->app->instance(ClickhouseManagerService::class, $manager);

        $this->artisan(ClickhouseMigrateCommand::class)
            ->expectsOutput('Cannot connect to ClickHouse. Check CLICKHOUSE_* env variables.')
            ->assertExitCode(1);
    }

    public function test_returns_success_when_no_pending_migrations_found(): void
    {
        /** @var MockInterface&ClickhouseManagerService $manager */
        $manager = Mockery::mock(ClickhouseManagerService::class);
        $manager->shouldReceive('ping')->once()->andReturn(true);
        $this->app->instance(ClickhouseManagerService::class, $manager);

        /** @var MockInterface&ClickhouseMigratorService $migrator */
        $migrator = Mockery::mock(ClickhouseMigratorService::class);
        $migrator->shouldReceive('ensureMigrationsTableExists')->once();
        $migrator->shouldReceive('getPendingMigrations')->once()->andReturn([]);
        $migrator->shouldNotReceive('runMigration');
        $this->app->instance(ClickhouseMigratorService::class, $migrator);

        $this->artisan(ClickhouseMigrateCommand::class)
            ->expectsOutput('No pending migrations found.')
            ->assertExitCode(0);
    }

    public function test_executes_pending_migration_files_successfully(): void
    {
        /** @var MockInterface&ClickhouseManagerService $manager */
        $manager = Mockery::mock(ClickhouseManagerService::class);
        $manager->shouldReceive('ping')->once()->andReturn(true);
        $this->app->instance(ClickhouseManagerService::class, $manager);

        $fakeFilePath = database_path('clickhouse/01_create_tables.sql');

        /** @var MockInterface&ClickhouseMigratorService $migrator */
        $migrator = Mockery::mock(ClickhouseMigratorService::class);
        $migrator->shouldReceive('ensureMigrationsTableExists')->once();
        $migrator->shouldReceive('getPendingMigrations')->once()->andReturn([$fakeFilePath]);
        $migrator->shouldReceive('runMigration')->once()->with($fakeFilePath);
        $this->app->instance(ClickhouseMigratorService::class, $migrator);

        $this->artisan(ClickhouseMigrateCommand::class)
            ->expectsOutputToContain('Running: 01_create_tables.sql')
            ->expectsOutputToContain('DONE')
            ->expectsOutput('ClickHouse migrations finished.')
            ->assertExitCode(0);
    }

    public function test_returns_failure_when_migration_throws_an_exception(): void
    {
        /** @var MockInterface&ClickhouseManagerService $manager */
        $manager = Mockery::mock(ClickhouseManagerService::class);
        $manager->shouldReceive('ping')->once()->andReturn(true);
        $this->app->instance(ClickhouseManagerService::class, $manager);

        $fakeFilePath = database_path('clickhouse/01_create_tables.sql');

        /** @var MockInterface&ClickhouseMigratorService $migrator */
        $migrator = Mockery::mock(ClickhouseMigratorService::class);
        $migrator->shouldReceive('ensureMigrationsTableExists')->once();
        $migrator->shouldReceive('getPendingMigrations')->once()->andReturn([$fakeFilePath]);
        $migrator->shouldReceive('runMigration')
            ->once()
            ->with($fakeFilePath)
            ->andThrow(new RuntimeException('Syntax error in SQL'));
        $this->app->instance(ClickhouseMigratorService::class, $migrator);

        $this->artisan(ClickhouseMigrateCommand::class)
            ->expectsOutputToContain('Running: 01_create_tables.sql')
            ->expectsOutput('Migration failed: 01_create_tables.sql')
            ->expectsOutput('Syntax error in SQL')
            ->assertExitCode(1);
    }
}
