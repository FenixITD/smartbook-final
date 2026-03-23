<?php

declare(strict_types=1);

namespace App\Http\Requests\Author;

use App\Dto\Author\AuthorDto;
use Illuminate\Foundation\Http\FormRequest;

final class AuthorDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function toDto(): AuthorDto
    {
        return new AuthorDto(
            name: $this->input('name'),
        );
    }
}
