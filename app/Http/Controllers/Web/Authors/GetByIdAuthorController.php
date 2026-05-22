<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Authors;

use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\View\View;

final readonly class GetByIdAuthorController
{
    public function __construct(
        private AuthorRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $authorId): View
    {
        $author = $this->repository->findByIdWithRelations($authorId);

        return view('authors.show', compact('author'));
    }
}
