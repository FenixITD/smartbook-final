<?php

declare(strict_types=1);

namespace App\Http\Requests\Review;

use App\Dto\Review\ReviewDto;
use Illuminate\Foundation\Http\FormRequest;

final class StorePublicReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function toDto(): ReviewDto
    {
        return new ReviewDto(
            userId: (int) auth()->id(),
            bookId: $this->integer('book_id'),
            rating: (float) $this->integer('rating'),
            comment: (string) $this->string('comment'),
        );
    }
}
