<?php

declare(strict_types=1);

namespace App\Dto\Author;

final readonly class AuthorDto
{
    public function __construct(
        public string $name,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
