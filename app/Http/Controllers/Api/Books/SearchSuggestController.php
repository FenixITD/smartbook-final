<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Books;

use App\Dto\Book\BookFiltersDto;
use App\Models\Book;
use App\Services\Elasticsearch\BookIndexService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class SearchSuggestController
{
    public function __construct(
        private BookIndexService $searchService,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string) $request->get('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $filters = new BookFiltersDto(search: $query, perPage: 5);
        $result = $this->searchService->search($filters);

        if (empty($result['ids'])) {
            return response()->json([]);
        }

        $ids = array_slice($result['ids'], 0, 5);
        $books = Book::with('author')
            ->whereIn('id', $ids)
            ->orderByRaw($this->orderByIds($ids))
            ->get();

        $suggestions = $books->map(fn (Book $book) => [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author?->name,
            'cover_image' => $book->cover_image,
            'price' => $book->price,
            'url' => route('dashboard', ['search' => $book->title]),
        ]);

        return response()->json($suggestions);
    }

    private function orderByIds(array $ids): string
    {
        $cases = collect($ids)
            ->values()
            ->map(fn (int $id, int $pos) => "WHEN id = {$id} THEN {$pos}")
            ->implode(' ');

        return 'CASE '.$cases.' ELSE '.count($ids).' END';
    }
}
