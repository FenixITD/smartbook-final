<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Authors;

use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\RedirectResponse;

final readonly class DeleteAuthorWebController
{
    public function __construct(
        private AuthorRepositoryInterface $repository,
    ) {}

    public function __invoke(int $author): RedirectResponse
    {
        $this->repository->delete($author);

        return redirect()->route('authors.index')
            ->with('success', 'Author deleted successfully.');
    }
}
