<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Clickhouse;

use App\Services\Clickhouse\ClickhouseManagerService;
use App\Services\Clickhouse\ClickhouseMigratorService;
use Illuminate\Filesystem\Filesystem;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ClickhouseMigratorServiceTest extends TestCase
{
    private ClickhouseManagerService&MockInterface $db;
    private Filesystem&MockInterface $files;
    private ClickhouseMigratorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Mockery::mock(ClickhouseManagerService::class);
        $this->files = Mockery::mock(Filesystem::class);
        $this->service = new ClickhouseMigratorService($this->db, $this->files);
    }

    public function test_ensure_migrations_table_exists(): void
    {
        $this->db->expects('execute')->with("CREATE TABLE IF NOT EXISTS clickhouse_migrations (migration String, executed_at DateTime DEFAULT now()) ENGINE = MergeTree() ORDER BY migration");

        $this->service->ensureMigrationsTableExists();
    }

    public function test_get_pending_migrations_returns_empty_when_path_does_not_exist(): void
    {
        $this->files->expects('exists')->with('path')->andReturn(false);

        $this->assertSame([], $this->service->getPendingMigrations('path'));
    }

    public function test_get_pending_migrations_returns_unexecuted_files(): void
    {
        $this->files->expects('exists')->with('path')->andReturn(true);
        $this->files->expects('glob')->with('path/*.sql')->andReturn(['path/1.sql', 'path/2.sql']);
        $this->db->expects('select')->with("SELECT migration FROM clickhouse_migrations")->andReturn([['migration' => '1.sql']]);

        $this->assertSame(['path/2.sql'], $this->service->getPendingMigrations('path'));
    }

    public function test_run_migration_executes_sql_and_inserts_record(): void
    {
        $this->files->expects('get')->with('path/1.sql')->andReturn("SELECT 1; SELECT 2;");
        $this->db->expects('execute')->with("SELECT 1");
        $this->db->expects('execute')->with("SELECT 2");
        $this->db->expects('insert')->with('clickhouse_migrations', ['migration' => '1.sql']);

        $this->service->runMigration('path/1.sql');
    }
}
