<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->unique(['user_id', 'book_id'], 'reviews_user_book_unique');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            //
        });
    }
};
