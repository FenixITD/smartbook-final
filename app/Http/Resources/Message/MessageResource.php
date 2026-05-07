<?php

declare(strict_types=1);

namespace App\Http\Resources\Message;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var Message $message */
        $message = $this->resource;

        return [
            'id' => $message->id,
            'body' => $message->body,
            'user_id' => $message->user_id,
            'sender_name' => $message->user?->name,
            'created_at' => $message->created_at?->toIso8601String(),
        ];
    }
}
