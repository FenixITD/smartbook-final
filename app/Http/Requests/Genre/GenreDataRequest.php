<?php

declare(strict_types=1);

namespace App\Http\Requests\Genre;

use App\Dto\Genre\GenreDto;
use Illuminate\Foundation\Http\FormRequest;

final class GenreDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/'],
            'description' => ['required', 'string'],
        ];
    }

    public function toDto(): GenreDto
    {
        return new GenreDto(
            name: $this->input('name'),
            slug: $this->input('slug'),
            description: $this->input('description'),
        );
    }
}
