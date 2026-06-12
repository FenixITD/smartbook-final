<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\SendEntityNotificationJob;
use App\Models\Review;

final class ReviewObserver
{
    public function created(Review $review): void
    {
        $review->loadMissing('user');

        SendEntityNotificationJob::dispatch(
            'Review',
            'created',
            [
                'id' => $review->id,
                'user_name' => $review->user->name ?? 'Unknown',
                'rating' => $review->rating,
                'comment' => $review->comment ?? '',
            ],
            now()->format('d.m.Y H:i:s')
        );
    }
}
