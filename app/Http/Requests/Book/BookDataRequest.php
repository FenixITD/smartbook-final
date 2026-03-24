<?php

declare(strict_types=1);

namespace App\Http\Requests\Book;

use App\Dto\Book\BookDto;
use Illuminate\Foundation\Http\FormRequest;

class BookDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
            'averageRating' => ['nullable', 'numeric', 'between:0,5'],
            'ratingsCount' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', 'in:draft,published,archived'],
        ];
    }

    public function toDto(): BookDto
    {
        return new BookDto(
            title: $this->input('title'),
            slug: $this->input('slug'),
            authorId: $this->integer('authorId'),
            description: $this->input('description'),
            price: $this->float('price'),
            stock: $this->integer('stock'),
            publishYear: $this->integer('publishYear'),
            coverImage: $this->input('coverImage'),
            averageRating: $this->float('averageRating'),
            ratingsCount: $this->integer('ratingsCount'),
            status: $this->input('status'),
        );
    }
}
