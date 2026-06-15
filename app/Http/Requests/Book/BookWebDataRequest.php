<?php

declare(strict_types=1);

namespace App\Http\Requests\Book;

use App\Dto\Book\BookDto;
use Illuminate\Foundation\Http\FormRequest;

use function is_array;

final class BookWebDataRequest extends FormRequest
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
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'string', 'in:draft,active,archived'],
            'genres' => ['nullable', 'array'],
            'genres.*' => ['integer', 'exists:genres,id'],
        ];
    }

    public function toDto(): BookDto
    {
        $coverImage = null;

        if ($this->hasFile('cover_image')) {
            /** @var string $stored */
            $stored = $this->file('cover_image')?->store('covers', 's3');
            $coverImage = $stored;
        }

        return new BookDto(
            title: (string) $this->string('title'),
            slug: (string) $this->string('slug'),
            authorId: $this->integer('authorId'),
            description: (string) $this->string('description'),
            price: (float) $this->string('price')->toString(),
            stock: $this->integer('stock'),
            publishYear: $this->integer('publishYear') !== 0 ? $this->integer('publishYear') : null,
            coverImage: $coverImage,
            status: (string) $this->string('status'),
        );
    }

    /** @return array<int, int> */
    public function genres(): array
    {
        $raw = $this->input('genres', []);

        if (!is_array($raw)) {
            return [];
        }

        return array_values(array_map(static fn (mixed $v): int => is_numeric($v) ? (int) $v : 0, $raw));
    }
}
