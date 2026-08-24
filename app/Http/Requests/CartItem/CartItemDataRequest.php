<?php

declare(strict_types=1);

namespace App\Http\Requests\CartItem;

use App\Dto\CartItem\CartItemDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CartItemDataRequest',
    required: ['userId', 'bookId', 'quantity'],
    properties: [
        new OA\Property(property: 'userId', type: 'integer', example: 3),
        new OA\Property(property: 'bookId', type: 'integer', example: 4),
        new OA\Property(property: 'quantity', type: 'integer', example: 13),
    ],
    type: 'object',
)]
class CartItemDataRequest extends FormRequest
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
            'bookId' => ['required', 'integer', Rule::exists('books', 'id')->where('status', 'active')],
            'quantity' => ['required', 'integer'],
        ];
    }

    public function toDto(): CartItemDto
    {
        return new CartItemDto(
            userId: $this->integer('userId'),
            bookId: $this->integer('bookId'),
            quantity: $this->integer('quantity'),
        );
    }
}
