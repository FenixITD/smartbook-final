<?php

declare(strict_types=1);

namespace App\DTO\Author;

use App\Http\Requests\Author\AuthorDataRequest;

final readonly class AuthorDTO
{
    public function __construct(
        public string $name,
    ) {}

    public static function fromRequest(AuthorDataRequest $request): self
    {
        return new self(
            name: (string) $request->string('name'),
        );
    }
}
