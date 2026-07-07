<?php

declare(strict_types=1);

namespace App\Dto\Genre;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class GenreDto implements Arrayable
{
    public function __construct(
        public string $name,
        public string $slug,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
}
