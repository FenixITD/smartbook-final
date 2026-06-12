<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Clickhouse\ClickhouseManagerService;
use App\Services\Clickhouse\ClickhouseMigratorService;
use Illuminate\Console\Command;
use Throwable;

use function is_string;

/**
 * The Laravel command-line tool (run via `php artisan clickhouse:migrate`) serves as the user
 * entry point for triggering database schema migrations. It is responsible solely for operations
 * within the command-line interface (CLI) and for handing off control.
 */
final class ClickhouseMigrateCommand extends Command
{
    protected $signature = 'clickhouse:migrate';

    protected $description = 'Run pending ClickHouse SQL migrations';

    public function handle(ClickhouseManagerService $clickhouseManagerService, ClickhouseMigratorService $migrator): int
    {
        if (!$clickhouseManagerService->ping()) {
            $this->error('Cannot connect to ClickHouse. Check CLICKHOUSE_* env variables.');

            return self::FAILURE;
        }

        $path = config('clickhouse.migrations_path', database_path('clickhouse'));
        $migrationPath = is_string($path) ? $path : database_path('clickhouse');

        try {
            $migrator->ensureMigrationsTableExists();

            /** @var array<int, string> $pendingFiles */
            $pendingFiles = $migrator->getPendingMigrations($migrationPath);

            if ($pendingFiles === []) {
                $this->info('No pending migrations found.');

                return self::SUCCESS;
            }

            foreach ($pendingFiles as $file) {
                $name = basename($file);
                $success = true;
                $errorMessage = null;

                $this->components->task("Running: {$name}", static function () use ($migrator, $file, &$success, &$errorMessage) {
                    try {
                        $migrator->runMigration($file);

                        return true;
                    } catch (Throwable $e) {
                        $success = false;
                        $errorMessage = $e->getMessage();

                        return false;
                    }
                });

                if (!$success) {
                    $this->error("Migration failed: {$name}");

                    if ($errorMessage !== null) {
                        $this->error($errorMessage);
                    }

                    return self::FAILURE;
                }
            }

            $this->info('ClickHouse migrations finished.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Migration process failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
