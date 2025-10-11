<?php

declare(strict_types=1);

use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

describe('MessageResource', function () {
    it('transforms message into expected array structure', function () {
        $user = User::factory()->create(['name' => 'John Doe']);
        $requestUser = User::factory()->create();
        $conversation = Conversation::factory()->create();
        $message = Message::factory()->create([
            'user_id' => $user->id,
            'conversation_id' => $conversation->id,
            'content' => 'Hello, World!',
            'type' => 'text',
            'metadata' => ['key' => 'value'],
            'read_at' => now(),
        ]);

        $message->load('user');

        $request = Request::create('/');
        $request->setUserResolver(function () use ($requestUser) {
            return $requestUser;
        });

        $resource = new MessageResource($message);
        $array = $resource->toArray($request);

        expect($array)
            ->toHaveKey('id', $message->id)
            ->toHaveKey('conversation_id', $message->conversation_id)
            ->toHaveKey('content', 'Hello, World!')
            ->toHaveKey('type', 'text')
            ->toHaveKey('metadata', ['key' => 'value'])
            ->toHaveKey('read_at')
            ->toHaveKey('created_at')
            ->toHaveKey('updated_at')
            ->toHaveKey('user')
            ->toHaveKey('is_read', true)
            ->toHaveKey('is_own', false)
            ->toHaveKey('local_id', null)
            ->toHaveKey('sync_status', 'synced');

        expect($array['user'])->toBeInstanceOf(App\Http\Resources\UserResource::class);
    });

    it('correctly identifies own messages', function () {
        $user = User::factory()->create();
        $conversation = Conversation::factory()->create();
        $message = Message::factory()->create([
            'user_id' => $user->id,
            'conversation_id' => $conversation->id,
        ]);

        $message->load('user');

        $request = Request::create('/');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $resource = new MessageResource($message);
        $array = $resource->toArray($request);

        expect($array['is_own'])->toBeTrue();
    });

    it('correctly identifies unread messages', function () {
        $user = User::factory()->create();
        $conversation = Conversation::factory()->create();
        $message = Message::factory()->unread()->create([
            'user_id' => $user->id,
            'conversation_id' => $conversation->id,
        ]);

        $message->load('user');

        $request = Request::create('/');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $resource = new MessageResource($message);
        $array = $resource->toArray($request);

        expect($array['is_read'])->toBeFalse();
    });

    it('handles null metadata', function () {
        $user = User::factory()->create();
        $conversation = Conversation::factory()->create();
        $message = Message::factory()->create([
            'user_id' => $user->id,
            'conversation_id' => $conversation->id,
            'metadata' => null,
        ]);

        $message->load('user');

        $request = Request::create('/');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $resource = new MessageResource($message);
        $array = $resource->toArray($request);

        expect($array['metadata'])->toBeNull();
    });

    it('handles unauthenticated requests', function () {
        $user = User::factory()->create();
        $conversation = Conversation::factory()->create();
        $message = Message::factory()->create([
            'user_id' => $user->id,
            'conversation_id' => $conversation->id,
        ]);

        $message->load('user');

        $request = Request::create('/');
        $request->setUserResolver(function () {
            return null;
        });

        $resource = new MessageResource($message);
        $array = $resource->toArray($request);

        expect($array['is_own'])->toBeFalse();
    });
});
