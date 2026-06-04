<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Clickhouse\ClickhouseManagerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class ClickhouseMigrateCommand extends Command
{
    protected $signature = 'clickhouse:migrate';

    protected $description = 'Run all ClickHouse SQL migrations from database/clickhouse/';

    public function handle(ClickhouseManagerService $clickhouseManagerService): int
    {
        if (!$clickhouseManagerService->ping()) {
            $this->error('Cannot connect to ClickHouse. Check CLICKHOUSE_* env variables.');

            return self::FAILURE;
        }

        /** @var array<int, string> $files */
        $files = File::glob(database_path('clickhouse/*.sql'));

        if ($files === []) {
            $this->info('No migration files found in database/clickhouse/');

            return self::SUCCESS;
        }

        sort($files);

        foreach ($files as $file) {
            $name = basename($file);
            $this->info("Running: {$name}");

            $sql = File::get($file);

            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                static fn (string $statement): bool => $statement !== ''
            );

            foreach ($statements as $statement) {
                $clickhouseManagerService->execute($statement);
            }

            $this->line('  <fg=green>Done</>');
        }

        $this->info('ClickHouse migrations finished.');

        return self::SUCCESS;
    }
}
