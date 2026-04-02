<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Authors;

use App\Models\Author;
use Illuminate\View\View;

final readonly class GetByIdAuthorWebController
{
    public function __invoke(Author $author): View
    {
        return view('authors.show', compact('author'));
    }
}
