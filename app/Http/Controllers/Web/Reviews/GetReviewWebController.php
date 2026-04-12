<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Reviews;

use App\Models\Review;
use Illuminate\View\View;

final readonly class GetReviewWebController
{
    public function __invoke(Review $review): View
    {
        return view('reviews.show', compact('review'));
    }
}
