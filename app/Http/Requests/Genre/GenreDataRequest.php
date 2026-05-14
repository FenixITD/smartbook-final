<?php

declare(strict_types=1);

namespace App\Http\Requests\Genre;

use App\Dto\Genre\GenreDto;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'GenreDataRequest',
    required: ['name', 'slug'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Genre name'),
        new OA\Property(property: 'slug', type: 'string', example: 'genre-name'),
    ],
    type: 'object',
)]
final class GenreDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/'],
        ];
    }

    public function toDto(): GenreDto
    {
        return new GenreDto(
            name: (string) $this->string('name'),
            slug: (string) $this->string('slug'),
        );
    }
}
