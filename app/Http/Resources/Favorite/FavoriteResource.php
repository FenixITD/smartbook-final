<?php

declare(strict_types=1);

namespace App\Http\Resources\Favorite;

use App\Dto\Favorite\FavoriteResponseDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'FavoriteResource',
    properties: [
        new OA\Property(property: 'userId', type: 'integer', example: 3),
        new OA\Property(property: 'bookId', type: 'integer', example: 4),
    ],
    type: 'object',
)]
/**
 * @mixin FavoriteResponseDto
 */
final class FavoriteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'bookId' => $this->bookId,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
