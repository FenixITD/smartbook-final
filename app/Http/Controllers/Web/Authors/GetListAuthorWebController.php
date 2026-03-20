<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Authors;

use App\Dto\Author\AuthorFiltersDto;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\View\View;

final readonly class GetListAuthorWebController
{
    public function __invoke(Request $request): View
    {
        $filters = new AuthorFiltersDto(
            search: $request->input('search'),
            perPage: (int) $request->input('perPage', 15),
            sortBy: $request->input('sortBy', 'id'),
            sortDirection: $request->input('sortDirection', 'asc'),
        );

        $authors = Author::query()
            ->when($filters->search, fn ($q) => $q->where('name', 'like', "%{$filters->search}%"))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage);

        return view('authors.list', compact('authors'));
    }
}
