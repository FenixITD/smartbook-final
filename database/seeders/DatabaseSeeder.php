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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('local')) {
            /**
             * Demo accounts with publicly known credentials — for local development only.
             * They are intentionally never created in other environments.
             */
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
        } else {
            $this->command->warn('APP_ENV is not "local": creating users with random passwords, skipping demo accounts.');
            User::factory()->count(10)->create([
                'password' => Hash::make(Str::random(40)),
            ]);
        }

        Author::factory()->count(10)->create();
        Genre::factory()->count(15)->create();

        Book::factory()->count(50)->create()->each(static function ($book): void {
            $genres = Genre::inRandomOrder()->limit(random_int(1, 4))->pluck('id');
            $book->genres()->attach($genres);
        });

        /**
         * Resolving the conflict between random data generation (faker) and strict database rules.
         */
        $users = User::all();

        foreach ($users as $user) {
            $reviewBookIds = Book::inRandomOrder()->limit(random_int(5, 8))->pluck('id');
            foreach ($reviewBookIds as $bookId) {
                Review::factory()->create([
                    'user_id' => $user->id,
                    'book_id' => $bookId,
                ]);
            }

            $favoriteBookIds = Book::inRandomOrder()->limit(2)->pluck('id');
            foreach ($favoriteBookIds as $bookId) {
                Favorite::factory()->create([
                    'user_id' => $user->id,
                    'book_id' => $bookId,
                ]);
            }

            $cartBookIds = Book::inRandomOrder()->limit(1)->pluck('id');
            foreach ($cartBookIds as $bookId) {
                CartItem::factory()->create([
                    'user_id' => $user->id,
                    'book_id' => $bookId,
                ]);
            }
        }

        Order::factory()->count(30)->create()->each(static function ($order): void {
            OrderItem::factory()->count(random_int(1, 6))->create([
                'order_id' => $order->id,
            ]);
        });

        /**
         * Assigning real ratings to books and their quantity.
         */
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
