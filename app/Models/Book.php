<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    use LogsActivity;
    use Searchable;

    protected $casts = [
        'price' => 'float',
        'average_rating' => 'float',
    ];

    protected $fillable = [
        'title',
        'slug',
        'author_id',
        'description',
        'price',
        'stock',
        'publish_year',
        'cover_image',
        'average_rating',
        'ratings_count',
        'status',
    ];

    public function searchableAs(): string
    {
        return 'books';
    }

    /** @return array<string, mixed> */
    public function toSearchableArray(): array
    {
        $this->loadMissing('genres');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'author_id' => $this->author_id,
            'genre_ids' => $this->genres->pluck('id')->all(),
            'price' => $this->price,
            'stock' => $this->stock,
            'publish_year' => (int) $this->publish_year,
            'cover_image' => $this->cover_image,
            'average_rating' => $this->average_rating,
            'ratings_count' => $this->ratings_count,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName(class_basename($this));
    }

    /** @return BelongsTo<Author, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    /** @return BelongsToMany<Genre, $this> */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    /** @return HasMany<Review, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** @return HasMany<Favorite, $this> */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /** @return HasMany<CartItem, $this> */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /** @return HasMany<OrderItem, $this> */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
