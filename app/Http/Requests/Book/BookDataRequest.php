<?php

declare(strict_types=1);

namespace App\Http\Requests\Book;

use App\Dto\Book\BookDto;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BookDataRequest',
    required: ['title', 'slug', 'authorId', 'description', 'price', 'stock', 'status'],
    properties: [
        new OA\Property(property: 'title', type: 'string', example: 'Book title'),
        new OA\Property(property: 'slug', type: 'string', example: 'book-title'),
        new OA\Property(property: 'authorId', type: 'integer', example: 3),
        new OA\Property(property: 'description', type: 'string', example: 'Book description'),
        new OA\Property(property: 'price', type: 'number', example: 16.99),
        new OA\Property(property: 'stock', type: 'integer', example: 8),
        new OA\Property(property: 'publishYear', type: 'integer', example: 2012),
        new OA\Property(property: 'coverImage', type: 'string', example: 'Book image'),
        new OA\Property(property: 'status', type: 'string', example: 'archived'),
    ],
    type: 'object',
)]
class BookDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/'],
            'authorId' => ['required', 'integer', 'exists:authors,id'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'stock' => ['required', 'integer', 'min:0'],
            'publishYear' => ['nullable', 'integer', 'min:1900', 'max:'.((int) date('Y') + 1)],
            'coverImage' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:draft,active,archived'],
        ];
    }

    public function toDto(): BookDto
    {
        return new BookDto(
            title: (string) $this->string('title'),
            slug: (string) $this->string('slug'),
            authorId: $this->integer('authorId'),
            description: (string) $this->string('description'),
            price: $this->float('price'),
            stock: $this->integer('stock'),
            publishYear: $this->integer('publishYear'),
            coverImage: $this->has('coverImage') ? (string) $this->string('coverImage') : null,
            status: (string) $this->string('status'),
        );
    }
}
