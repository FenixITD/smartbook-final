<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $keepIds = DB::table('reviews')
                ->selectRaw('MIN(id) as id')
                ->groupBy('user_id', 'book_id')
                ->pluck('id');

            DB::table('reviews')->whereNotIn('id', $keepIds)->delete();

            Schema::table('reviews', function (Blueprint $table) {
                $table->unique(['user_id', 'book_id'], 'reviews_user_book_unique');
            });
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique('reviews_user_book_unique');
        });
    }
};
