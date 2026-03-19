<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\DTO\Book\BookDto;
use App\DTO\Book\BookResponseDto;
use App\Repositories\Interfaces\BookRepositoryInterface;

final readonly class CreateBookService
{
    public function __construct(
        private BookRepositoryInterface $repository
    ) {}

    public function execute(BookDto $dto): BookResponseDto
    {
        return $this->repository->create([
            'title' => $dto->title,
            'slug' => $dto->slug ?? str($dto->title)->slug(),
            'authorId' => $dto->authorId,
            'description' => $dto->description,
            'price' => $dto->price,
            'stock' => $dto->stock,
            'publishYear' => $dto->publishYear,
            'coverImage' => $dto->coverImage,
            'averageRating' => $dto->averageRating,
            'ratingsCount' => $dto->ratingsCount,
            'status' => $dto->status,
        ]);
    }
}
