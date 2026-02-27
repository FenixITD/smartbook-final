<?php

namespace App\DTO;

use App\Http\Requests\AuthorRequest;

final readonly class AuthorDTO
{
    public function __construct(
        public string $name,
    ) {}

    public static function fromRequest(AuthorRequest $request): self
    {
        return new self(
            name: $request->string('name'),
        );
    }
}
