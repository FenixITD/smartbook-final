<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Authors;

use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\RedirectResponse;

final readonly class DeleteAuthorController
{
    public function __construct(
        private AuthorRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $authorId): RedirectResponse
    {
        $this->repository->delete($authorId);

        return redirect()->route('authors.index')
            ->with('success', 'Author deleted successfully.');
    }
}
