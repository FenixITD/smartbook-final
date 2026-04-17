<?php

declare(strict_types=1);

namespace App\Http\Requests\Review;

use App\Dto\Review\ReviewDto;
use Illuminate\Foundation\Http\FormRequest;

class ReviewDataRequest extends FormRequest
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
            'rating' => ['required', 'numeric', 'min:0', 'max:5'],
            'comment' => ['nullable', 'string'],
        ];
    }

    public function toDto(): ReviewDto
    {
        return new ReviewDto(
            userId: $this->integer('userId'),
            bookId: $this->integer('bookId'),
            rating: $this->float('rating'),
            comment: (string) $this->string('comment'),
        );
    }
}
