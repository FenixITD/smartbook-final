<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\SendEntityNotificationJob;
use App\Models\Review;
use App\Repositories\Interfaces\BookRepositoryInterface;

final class ReviewObserver
{
    public function __construct(
        private BookRepositoryInterface $bookRepository,
    ) {
    }

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

        $this->bookRepository->recalculateRating($review->book_id);
    }

    public function updated(Review $review): void
    {
        $bookChanged = $review->wasChanged('book_id');
        $ratingChanged = $review->wasChanged('rating');

        if (! $bookChanged && ! $ratingChanged) {
            return;
        }

        if ($bookChanged) {
            $originalRaw = $review->getOriginal('book_id');

            if (is_int($originalRaw) || is_string($originalRaw)) {
                $this->bookRepository->recalculateRating((int) $originalRaw);
            }
        }

        $this->bookRepository->recalculateRating($review->book_id);
    }

    public function deleted(Review $review): void
    {
        $this->bookRepository->recalculateRating($review->book_id);
    }
}
