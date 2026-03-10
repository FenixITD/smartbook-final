<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\DTO\Book\BookDTO;
use App\DTO\Book\BookResponseDTO;
use App\Models\Book;
use App\Repositories\Interfaces\BookRepositoryInterface;

final readonly class UpdateBookService
{
    public function __construct(
        private BookRepositoryInterface $repository
    ) {}

    public function execute(Book $book, BookDTO $dto): BookResponseDTO
    {
        $this->repository->update($book, [
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

        return BookResponseDTO::fromModel($book->fresh());
    }
}
