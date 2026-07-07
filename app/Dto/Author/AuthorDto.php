<?php

declare(strict_types=1);

namespace App\Dto\Author;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class AuthorDto implements Arrayable
{
    public function __construct(
        public string $name,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
