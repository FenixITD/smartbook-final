<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\PaginatedResponseDto;
use App\Dto\Review\BookReviewResponseDto;
use App\Dto\Review\ReviewDto;
use App\Dto\Review\ReviewFiltersDto;
use App\Dto\Review\ReviewResponseDto;
use App\Models\Review;
use App\Models\User;
use App\Repositories\Interfaces\ReviewRepositoryInterface;

final class ReviewRepository implements ReviewRepositoryInterface
{
    /** @return array<ReviewResponseDto> */
    public function getList(ReviewFiltersDto $filters): array
    {
        $query = Review::query()
            ->with('user:id,name')
            ->when($filters->id !== null, static fn ($q) => $q->where('id', $filters->id));

        if ($filters->sortBy === 'user_name') {
            $query->orderBy(
                User::select('name')->whereColumn('users.id', 'reviews.user_id'),
                $filters->sortDirection
            );
        } else {
            $query->orderBy($filters->sortBy, $filters->sortDirection);
        }

        return $query->paginate($filters->perPage)
            ->getCollection()
            ->map(static fn (Review $review) => ReviewResponseDto::fromModel($review))
            ->all();
    }

    public function getWebList(ReviewFiltersDto $filters): PaginatedResponseDto
    {
        $query = Review::query()
            ->with(['user:id,name', 'book:id,title']);

        if ($filters->sortBy === 'user_name') {
            $query->orderBy(
                User::select('name')->whereColumn('users.id', 'reviews.user_id'),
                $filters->sortDirection
            );
        } else {
            $query->orderBy($filters->sortBy, $filters->sortDirection);
        }

        $paginator = $query->paginate($filters->perPage)->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator);
    }

    /** @param array<int> $ids */
    public function getWebListByIds(array $ids, ReviewFiltersDto $filters): PaginatedResponseDto
    {
        $query = Review::query()
            ->with(['user:id,name', 'book:id,title'])
            ->whereIn('id', $ids);

        if ($filters->sortBy === 'user_name') {
            $query->orderBy(
                User::select('name')->whereColumn('users.id', 'reviews.user_id'),
                $filters->sortDirection
            );
        } else {
            $query->orderBy($filters->sortBy, $filters->sortDirection);
        }

        $paginator = $query->paginate($filters->perPage)->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator);
    }

    public function getById(int $id): ReviewResponseDto|null
    {
        $reviewId = Review::find($id);

        return $reviewId !== null ? ReviewResponseDto::fromModel($reviewId) : null;
    }

    public function findByIdWithRelations(int $id): ReviewResponseDto
    {
        $reviewId = Review::with(['user', 'book'])
            ->findOrFail($id);

        return ReviewResponseDto::fromModel($reviewId);
    }

    public function getByIds(array $ids): array
    {
        return Review::with('user')
            ->whereIn('id', $ids)
            ->get()
            ->map(static fn (Review $review) => ReviewResponseDto::fromModel($review))
            ->all();
    }

    public function create(ReviewDto $data): ReviewResponseDto
    {
        /** @var Review $review */
        $review = Review::create($data->toArray());

        return ReviewResponseDto::fromModel($review);
    }

    public function update(int $id, ReviewDto $data): ReviewResponseDto
    {
        $review = Review::findOrFail($id);

        $review->update($data->toArray());

        /** @var Review $fresh */
        $fresh = $review->fresh();

        return ReviewResponseDto::fromModel($fresh);
    }

    public function delete(int $id): bool
    {
        return (bool) Review::findOrFail($id)->delete();
    }

    public function getByBookId(int $bookId, int $perPage = 10): PaginatedResponseDto
    {
        $paginator = Review::with('user:id,name')
            ->where('book_id', $bookId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $items = $paginator->getCollection()
            ->map(static fn (Review $review) => BookReviewResponseDto::fromModel($review))
            ->all();

        return new PaginatedResponseDto(
            items: $items,
            total: $paginator->total(),
            perPage: $paginator->perPage(),
            currentPage: $paginator->currentPage(),
            lastPage: $paginator->lastPage(),
            links: $paginator->links()->toHtml(),
        );
    }

    public function findByUserAndBook(int $userId, int $bookId): ReviewResponseDto|null
    {
        $review = Review::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->with('user:id,name')
            ->first();

        return $review !== null ? ReviewResponseDto::fromModel($review) : null;
    }
}
