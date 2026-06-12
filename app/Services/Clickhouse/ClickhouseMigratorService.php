<?php

declare(strict_types=1);

namespace App\Services\Clickhouse;

use Illuminate\Filesystem\Filesystem;
use RuntimeException;

use function in_array;

class ClickhouseMigratorService
{
    public function __construct(
        private ClickhouseManagerService $db,
        private Filesystem $files,
    ) {
    }

    public function ensureMigrationsTableExists(): void
    {
        $this->db->execute('CREATE TABLE IF NOT EXISTS clickhouse_migrations (migration String, executed_at DateTime DEFAULT now()) ENGINE = MergeTree() ORDER BY migration');
    }

    /**
     * @return array<int, string>
     */
    public function getPendingMigrations(string $path): array
    {
        if (!$this->files->exists($path)) {
            return [];
        }

        /** @var array<int, string> $allFiles */
        $allFiles = $this->files->glob($path.'/*.sql');
        sort($allFiles);

        $executed = $this->db->select('SELECT migration FROM clickhouse_migrations');
        $executedNames = array_column($executed, 'migration');

        $pending = [];

        /** @var string $file */
        foreach ($allFiles as $file) {
            if (!in_array(basename($file), $executedNames, true)) {
                $pending[] = $file;
            }
        }

        return $pending;
    }

    public function runMigration(string $file): void
    {
        $sql = $this->files->get($file);

        $statements = preg_split('/;(?=(?:[^\']*\'[^\']*\')*[^\']*$)/', $sql);

        if ($statements === false) {
            throw new RuntimeException('Failed to parse migration: '.basename($file));
        }

        foreach ($statements as $statement) {
            $statement = trim($statement);

            if ($statement !== '') {
                $this->db->execute($statement);
            }
        }

        $this->db->insert('clickhouse_migrations', [
            'migration' => basename($file),
        ]);
    }
}
