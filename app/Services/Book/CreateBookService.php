<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\DTO\Book\BookDTO;
use App\DTO\Book\BookResponseDTO;
use App\Repositories\Interfaces\BookRepositoryInterface;

final readonly class CreateBookService
{
    public function __construct(
        private BookRepositoryInterface $repository
    ) {}

    public function execute(BookDTO $dto): BookResponseDTO
    {
        return $this->repository->create([
            'title' => $dto->title,
            'slug' => $dto->slug ?? str($dto->title)->slug(),
            'author_id' => $dto->author_id,
            'description' => $dto->description,
            'price' => $dto->price,
            'stock' => $dto->stock,
            'publish_year' => $dto->publish_year,
            'cover_image' => $dto->cover_image,
            'average_rating' => $dto->average_rating,
            'ratings_count' => $dto->ratings_count,
            'status' => $dto->status,
        ]);
    }
}
