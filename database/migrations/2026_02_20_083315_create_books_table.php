<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('books', static function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('author_id')->index()->constrained()->onDelete('restrict');
            $table->text('description');
            $table->decimal('price', 6, 2);
            $table->integer('stock')->default(0);
            $table->integer('publish_year')->nullable();
            $table->string('cover_image')->nullable();
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->integer('ratings_count')->default(0);
            $table->enum('status', ['published', 'draft', 'archived'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
