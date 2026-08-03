<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Books;

use App\Dto\Book\BookResponseDto;

trait BookResponseDtoFactory
{
    protected function makeBookResponseDto(
        int $id = 1,
        string $title = 'Test Book',
        string $slug = 'test-book',
        int $authorId = 1,
        string|null $authorName = 'Test Author',
        string $description = 'Test description',
        string $price = '19.99',
        int $stock = 10,
        int|null $publishYear = 2020,
        string|null $coverImage = null,
        float|null $averageRating = 4.5,
        int|null $ratingsCount = 100,
        string $status = 'active',
        string $createdAt = '2024-01-01 00:00:00',
        string $updatedAt = '2024-01-01 00:00:00',
        array $genres = [],
    ): BookResponseDto {
        return new BookResponseDto(
            id: $id,
            title: $title,
            slug: $slug,
            authorId: $authorId,
            authorName: $authorName,
            description: $description,
            price: $price,
            stock: $stock,
            publishYear: $publishYear,
            coverImage: $coverImage,
            averageRating: $averageRating,
            ratingsCount: $ratingsCount,
            status: $status,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            genres: $genres,
        );
    }

    /** @return array<string, mixed> */
    protected function validBookRequestData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Test Book',
            'slug' => 'test-book',
            'authorId' => 1,
            'description' => 'Test description',
            'price' => '19.99',
            'stock' => 10,
            'publishYear' => 2020,
            'coverImage' => null,
            'status' => 'active',
        ], $overrides);
    }
}
