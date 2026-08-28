<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_price_is_indexed_as_float(): void
    {
        $book = new Book(['title' => 'Test', 'slug' => 'test', 'price' => '19.99']);

        $searchable = $book->toSearchableArray();

        $this->assertIsFloat($searchable['price']);
        $this->assertSame(19.99, $searchable['price']);
    }
}
