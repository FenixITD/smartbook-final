<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Resources\Message;

use App\Http\Resources\Message\MessageResource;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class MessageResourceTest extends TestCase
{
    public function test_it_transforms_message_with_user_and_created_at(): void
    {
        $user = new User();
        $user->name = 'John Doe';

        $now = Carbon::now();

        $message = new Message();
        $message->id = 1;
        $message->body = 'Hello World';
        $message->user_id = 5;
        $message->setRelation('user', $user);
        $message->created_at = $now;

        $resource = new MessageResource($message);
        $request = Request::create('/', 'GET');
        $result = $resource->toArray($request);

        $this->assertSame([
            'id' => 1,
            'body' => 'Hello World',
            'user_id' => 5,
            'sender_name' => 'John Doe',
            'created_at' => $now->toIso8601String(),
        ], $result);
    }

    public function test_it_transforms_message_without_user_and_created_at(): void
    {
        $message = new Message();
        $message->id = 2;
        $message->body = 'Secret Message';
        $message->user_id = 6;
        $message->setRelation('user', null);
        $message->created_at = null;

        $resource = new MessageResource($message);
        $request = Request::create('/', 'GET');
        $result = $resource->toArray($request);

        $this->assertSame([
            'id' => 2,
            'body' => 'Secret Message',
            'user_id' => 6,
            'sender_name' => null,
            'created_at' => null,
        ], $result);
    }
}
