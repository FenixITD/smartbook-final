<?php

declare(strict_types=1);

namespace App\Http\Requests\Favorite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

final class FavoriteShowWebRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
