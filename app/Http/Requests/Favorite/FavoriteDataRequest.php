<?php

declare(strict_types=1);

namespace App\Http\Requests\Favorite;

use App\Dto\Favorite\FavoriteDto;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'FavoriteDataRequest',
    required: ['userId', 'bookId'],
    properties: [
        new OA\Property(property: 'userId', type: 'integer', example: 3),
        new OA\Property(property: 'bookId', type: 'integer', example: 4),
    ],
    type: 'object',
)]
class FavoriteDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'userId' => ['required', 'integer', 'exists:users,id'],
            'bookId' => ['required', 'integer', 'exists:books,id'],
        ];
    }

    public function toDto(): FavoriteDto
    {
        return new FavoriteDto(
            userId: $this->integer('userId'),
            bookId: $this->integer('bookId'),
        );
    }
}
