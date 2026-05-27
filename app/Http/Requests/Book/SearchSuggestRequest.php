<?php

declare(strict_types=1);

namespace App\Http\Requests\Book;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SearchSuggestRequest',
    required: ['q'],
    properties: [
        new OA\Property(property: 'q', type: 'string', minLength: 2, example: 'harry'),
    ],
    type: 'object',
)]
final class SearchSuggestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:2'],
        ];
    }

    public function searchQuery(): string
    {
        return trim((string) $this->string('q'));
    }
}
