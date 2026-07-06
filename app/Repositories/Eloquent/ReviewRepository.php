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

final class ReviewRepository extends AbstractEloquentRepository implements ReviewRepositoryInterface
{
    protected function getModelClass(): string
    {
        return Review::class;
    }

    protected function getResponseDtoClass(): string
    {
        return ReviewResponseDto::class;
    }

    public function getList(ReviewFiltersDto $filters): array
    {
        $query = $this->query()
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
        $query = $this->query()->with(['user:id,name', 'book:id,title']);

        if ($filters->sortBy === 'user_name') {
            $query->orderBy(
                User::select('name')->whereColumn('users.id', 'reviews.user_id'),
                $filters->sortDirection
            );
        } else {
            $query->orderBy($filters->sortBy, $filters->sortDirection);
        }

        $paginator = $query->paginate($filters->perPage)->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator, static fn (Review $review) => ReviewResponseDto::fromModel($review));
    }

    public function getWebListByIds(array $ids, int $total, ReviewFiltersDto $filters): PaginatedResponseDto
    {
        $query = $this->query()
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

        $items = $query->get();

        return $this->createPaginatedResponse($items, $total, $filters->perPage, static fn (Review $review) => ReviewResponseDto::fromModel($review));
    }

    public function getById(int $id): ReviewResponseDto|null
    {
        return $this->executeGetById($id);
    }

    public function findByIdWithRelations(int $id): ReviewResponseDto
    {
        /** @var Review $review */
        $review = $this->query()->with(['user', 'book'])->findOrFail($id);

        return ReviewResponseDto::fromModel($review);
    }

    public function getByIds(array $ids): array
    {
        return $this->query()->with('user')
            ->whereIn('id', $ids)
            ->get()
            ->map(static fn (Review $review) => ReviewResponseDto::fromModel($review))
            ->all();
    }

    public function create(ReviewDto $data): ReviewResponseDto
    {
        return $this->executeCreate($data);
    }

    public function update(int $id, ReviewDto $data): ReviewResponseDto|null
    {
        return $this->executeUpdate($id, $data);
    }

    public function getByBookId(int $bookId, int $perPage = 10): PaginatedResponseDto
    {
        $paginator = $this->query()->with('user:id,name')
            ->where('book_id', $bookId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return PaginatedResponseDto::fromPaginator($paginator, static fn (Review $review) => BookReviewResponseDto::fromModel($review));
    }

    public function findByUserAndBook(int $userId, int $bookId): ReviewResponseDto|null
    {
        /** @var Review|null $review */
        $review = $this->query()->where('user_id', $userId)
            ->where('book_id', $bookId)
            ->with('user:id,name')
            ->first();

        return $review !== null ? ReviewResponseDto::fromModel($review) : null;
    }
}
