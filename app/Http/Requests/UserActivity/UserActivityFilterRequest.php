<?php

declare(strict_types=1);

namespace App\Http\Requests\UserActivity;

use Illuminate\Foundation\Http\FormRequest;

final class UserActivityFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'perPage' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
