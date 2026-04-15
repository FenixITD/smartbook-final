<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Book;
use App\Services\Elasticsearch\BookIndexService;
use Illuminate\Console\Command;

final class IndexBooksCommand extends Command
{
    protected $signature = 'books:index
                            {--reset : Drop and recreate the index before indexing}';

    protected $description = 'Index all books in Elasticsearch';

    public function __construct(
        private readonly BookIndexService $indexService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if ($this->option('reset')) {
            $this->info('Resetting Elasticsearch index...');
            $this->indexService->resetIndex();
            $this->info('Index reset successfully.');
        } else {
            $this->indexService->createIndexIfNotExists();
        }

        $total = Book::count();
        $this->info("Indexing {$total} books...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        Book::query()
            ->chunkById(500, function ($books) use ($bar) {
                $this->indexService->bulkIndex($books);
                $bar->advance($books->count());
            });

        $bar->finish();
        $this->newLine();
        $this->info('Books indexed successfully.');

        return self::SUCCESS;
    }
}
