<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Genre> */
class GenreFactory extends Factory
{
    private const GENRES = [
        'Action', 'Adventure', 'Fantasy', 'Science Fiction', 'Cyberpunk', 'Post-Apocalyptic', 'Mystery',
        'Horror', 'Thriller', 'Detective', 'Romance', 'Comedy', 'Psychology', 'Philosophy', 'Novella',
    ];

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(self::GENRES);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
