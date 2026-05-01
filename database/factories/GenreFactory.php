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
        'Action', 'Adventure', 'Fantasy', 'Science Fiction', 'Cyberpunk', 'Post-Apocalyptic', 'Space Opera',
        'Mystery', 'Horror', 'Thriller', 'Psychological Thriller', 'Crime Novel', 'Detective', 'Historical Novel',
        'Romance', 'Romantic Comedy', 'Erotica', 'Family Saga', 'Social Drama', 'Coming-of-Age', 'Middle Grade',
        'Poetry', 'Drama/Play', 'Comedy', 'Travelogue', 'Self-Help', 'Psychology', 'Popular Science', 'Philosophy',
        'Cookbook', 'Home & Garden', 'Art & Design', 'Novella', 'Experimental Fiction', 'Music Biography',
    ];

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(self::GENRES);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(2),
        ];
    }
}
