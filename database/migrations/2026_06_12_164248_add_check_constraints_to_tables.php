<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE books ADD CONSTRAINT chk_books_price CHECK (price >= 0)');
            DB::statement('ALTER TABLE books ADD CONSTRAINT chk_books_stock CHECK (stock >= 0)');
            DB::statement('ALTER TABLE books ADD CONSTRAINT chk_books_rating CHECK (average_rating >= 0 AND average_rating <= 5)');

            DB::statement('ALTER TABLE order_items ADD CONSTRAINT chk_order_items_quantity CHECK (quantity > 0)');
            DB::statement('ALTER TABLE order_items ADD CONSTRAINT chk_order_items_price CHECK (price_at_purchase >= 0)');

            DB::statement('ALTER TABLE reviews ADD CONSTRAINT chk_reviews_rating CHECK (rating >= 0 AND rating <= 5)');
            DB::statement('ALTER TABLE cart_items ADD CONSTRAINT chk_cart_items_quantity CHECK (quantity > 0)');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE books DROP CONSTRAINT chk_books_price');
            DB::statement('ALTER TABLE books DROP CONSTRAINT chk_books_stock');
            DB::statement('ALTER TABLE books DROP CONSTRAINT chk_books_rating');

            DB::statement('ALTER TABLE order_items DROP CONSTRAINT chk_order_items_quantity');
            DB::statement('ALTER TABLE order_items DROP CONSTRAINT chk_order_items_price');

            DB::statement('ALTER TABLE reviews DROP CONSTRAINT chk_reviews_rating');
            DB::statement('ALTER TABLE cart_items DROP CONSTRAINT chk_cart_items_quantity');
        }
    }
};
