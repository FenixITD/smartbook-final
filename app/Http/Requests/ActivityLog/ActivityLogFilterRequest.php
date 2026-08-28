<?php

declare(strict_types=1);

namespace App\Http\Requests\ActivityLog;

use App\Dto\ActivityLog\ActivityLogFiltersDto;
use App\Models\Author;
use App\Models\Book;
use App\Models\Conversation;
use App\Models\Favorite;
use App\Models\Genre;
use App\Models\Message;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;

final class ActivityLogFilterRequest extends FormRequest
{
    /** @var array<string, class-string> */
    public const SUBJECT_TYPE_MAP = [
        'Book' => Book::class,
        'Author' => Author::class,
        'Genre' => Genre::class,
        'Order' => Order::class,
        'Review' => Review::class,
        'Conversation' => Conversation::class,
        'Message' => Message::class,
        'Favorite' => Favorite::class,
    ];

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'perPage' => ['nullable', 'integer', 'min:1', 'max:100'],
            'subjectType' => ['nullable', 'string', 'in:'.implode(',', array_keys(self::SUBJECT_TYPE_MAP))],
        ];
    }

    public function toDto(): ActivityLogFiltersDto
    {
        $subjectKey = $this->filled('subjectType') ? $this->string('subjectType')->toString() : null;

        return new ActivityLogFiltersDto(
            page: $this->integer('page', 1),
            perPage: $this->integer('perPage', 20),
            subjectType: $subjectKey !== null ? (self::SUBJECT_TYPE_MAP[$subjectKey] ?? null) : null,
        );
    }
}
