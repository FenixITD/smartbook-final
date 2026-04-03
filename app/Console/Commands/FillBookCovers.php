<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Book;
use App\Services\Book\CoverImageService;
use Illuminate\Console\Command;

class FillBookCovers extends Command
{
    protected $signature = 'books:fill-covers';

    protected $description = 'Generate and upload placeholder covers to S3 for books without cover images';

    public function __construct(
        private readonly CoverImageService $coverImageService,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $books = Book::where(fn ($q) => $q->whereNull('cover_image')->orWhere('cover_image', ''))->get();

        $this->info("Found {$books->count()} books without covers.");

        $bar = $this->output->createProgressBar($books->count());
        $bar->start();

        foreach ($books as $book) {
            try {
                $path = $this->coverImageService->generatePlaceholder($book->title, $book->id);
                $book->update(['cover_image' => $path]);
            } catch (\Exception $e) {
                $this->newLine();
                $this->warn("Failed for book #{$book->id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done!');
    }
}
