<?php

declare(strict_types=1);

namespace App\Http\Requests\Book;

use App\Dto\Book\BookDto;
use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;

final class BookWebDataRequest extends FormRequest
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
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'string', 'in:active,draft,archived'],
            'genres' => ['nullable', 'array'],
            'genres.*' => ['integer', 'exists:genres,id'],
        ];
    }

    public function toDto(): BookDto
    {
        $coverImage = null;

        if ($this->hasFile('cover_image')) {
            $coverImage = $this->file('cover_image')->store('covers', 'public');
        }

        return new BookDto(
            title: $this->input('title'),
            slug: $this->input('slug'),
            authorId: $this->integer('authorId'),
            description: $this->input('description'),
            price: (float) $this->input('price'),
            stock: $this->integer('stock'),
            publishYear: $this->integer('publishYear') ?: null,
            coverImage: $coverImage,
            averageRating: null,
            ratingsCount: null,
            status: $this->input('status'),
        );
    }

    public function toDtoForUpdate(Book $book): BookDto
    {
        $coverImage = $this->hasFile('cover_image')
            ? $this->file('cover_image')->store('covers', 'public')
            : $book->cover_image;

        return new BookDto(
            title: $this->input('title'),
            slug: $this->input('slug'),
            authorId: $this->integer('authorId'),
            description: $this->input('description'),
            price: (float) $this->input('price'),
            stock: $this->integer('stock'),
            publishYear: $this->integer('publishYear') ?: null,
            coverImage: $coverImage,
            averageRating: (float) $book->average_rating,
            ratingsCount: (int) $book->ratings_count,
            status: $this->input('status'),
        );
    }

    public function genres(): array
    {
        return $this->input('genres', []);
    }
}
