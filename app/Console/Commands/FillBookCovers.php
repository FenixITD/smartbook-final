<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Book;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FillBookCovers extends Command
{
    protected $signature = 'books:fill-covers
        {--all : Update covers for ALL books}';

    protected $description = 'Download book covers for all books';

    public function handle(): void
    {
        $query = Book::query();

        if (! $this->option('all')) {
            $query->where(function ($q) {
                $q->whereNull('cover_image')
                    ->orWhere('cover_image', '')
                    ->orWhere('cover_image', ' ');
            });
        }

        $books = $query->get();
        $total = $books->count();

        if ($total === 0) {
            $this->info('There are no books to update covers for.');

            return;
        }

        $this->info("Starting to update the covers for {$total} books...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;

        foreach ($books as $book) {
            try {
                $title = Str::slug($book->title ?? 'book-'.$book->id);
                $author = Str::slug(optional($book->author)->name ?? 'author');

                // Sources for book covers
                $imageUrls = [
                    'https://picsum.photos/id/'.rand(100, 1000).'/400/600',
                    'https://source.unsplash.com/400x600/?book,reading,library,novel',
                    "https://picsum.photos/seed/book-{$book->id}/400/600",
                    'https://placehold.co/400x600/2a2a2a/ffffff/png?text='.urlencode(strtoupper(substr($book->title, 0, 15))),
                ];

                $downloaded = false;

                foreach ($imageUrls as $url) {
                    $response = Http::timeout(25)
                        ->withHeaders([
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        ])
                        ->get($url);

                    if ($response->successful() && $response->body() && strlen($response->body()) > 5000) {
                        // Deleting old cover
                        if ($book->cover_image && Storage::disk('public')->exists($book->cover_image)) {
                            Storage::disk('public')->delete($book->cover_image);
                        }

                        $filename = 'covers/'.Str::uuid().'.jpg';

                        Storage::disk('public')->put($filename, $response->body());

                        $book->update(['cover_image' => $filename]);

                        $success++;
                        $downloaded = true;
                        $this->line("  ✓ Book #{$book->id} — cover updated");
                        break;
                    }
                }

                if (! $downloaded) {
                    $this->warn("  ✗ Failed to download book cover #{$book->id}");
                }
            } catch (\Exception $e) {
                $this->warn("  ✗ Book error #{$book->id}: ".$e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done! Covers successfully updated: {$success} from {$total}");
    }
}
