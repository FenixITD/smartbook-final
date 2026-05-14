<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\CartItem;
use App\Models\Favorite;
use App\Models\Genre;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->count(10)->create();
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@smartbook.com',
            'password' => bcrypt('admin123'),
        ]);
        User::factory()->user()->create([
            'name' => 'User',
            'email' => 'user@smartbook.com',
            'password' => bcrypt('user123'),
        ]);

        Author::factory()->count(10)->create();
        Genre::factory()->count(15)->create();

        Book::factory()->count(50)->create()->each(static function ($book): void {
            $genres = Genre::inRandomOrder()->limit(random_int(1, 4))->pluck('id');
            $book->genres()->attach($genres);
        });

        Review::factory()->count(80)->create();
        Favorite::factory()->count(20)->create();
        CartItem::factory()->count(10)->create();

        Order::factory()->count(30)->create()->each(static function ($order): void {
            OrderItem::factory()->count(random_int(1, 6))->create([
                'order_id' => $order->id,
            ]);
        });

        Book::each(function (Book $book): void {
            $book->update([
                'ratings_count' => $book->reviews()->count(),
                'average_rating' => $book->reviews()->avg('rating') ?? 0,
            ]);
        });

        $this->command->info('Reindexing Elasticsearch...');
        Artisan::call('scout:import', ['model' => 'App\Models\Book']);
        Artisan::call('scout:import', ['model' => 'App\Models\Author']);
        Artisan::call('scout:import', ['model' => 'App\Models\Genre']);
        Artisan::call('scout:import', ['model' => 'App\Models\Order']);
        Artisan::call('scout:import', ['model' => 'App\Models\Review']);
    }
}
