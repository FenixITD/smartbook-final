<?php

declare(strict_types=1);

namespace App\Http\Resources\Message;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'user_id' => $this->user_id,
            'sender_name' => $this->user->name,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
