<?php

declare(strict_types=1);

namespace App\Http\Requests\Book;

use Illuminate\Foundation\Http\FormRequest;

final class BookDataRequest extends FormRequest
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
            'author_id' => ['required', 'integer', 'exists:authors,id'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'stock' => ['required', 'integer', 'min:0'],
            'publish_year' => ['nullable', 'integer', 'min:1900', 'max:'.((int) date('Y') + 1)],
            'cover_image' => ['nullable', 'string', 'max:255'],
            'average_rating' => ['nullable', 'numeric', 'between:0,5'],
            'ratings_count' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', 'in:draft,published,archived'],
        ];
    }
}
