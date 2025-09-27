<?php

declare(strict_types=1);

use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

describe('ConversationResource', function () {
    it('transforms conversation into expected array structure', function () {
        $user = User::factory()->create(['name' => 'John Doe']);
        $otherUser = User::factory()->create(['name' => 'Jane Smith']);
        $conversation = Conversation::factory()->create([
            'title' => 'Test Conversation',
            'type' => 'group',
            'created_by' => $user->id,
        ]);

        $conversation->participants()->attach([
            $user->id => ['joined_at' => now(), 'is_admin' => true],
            $otherUser->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        $lastMessage = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $otherUser->id,
            'content' => 'Last message',
        ]);

        $conversation->load(['participants', 'messages' => function ($query) {
            $query->latest()->limit(1);
        }]);

        $request = Request::create('/');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $resource = new ConversationResource($conversation);
        $array = $resource->toArray($request);

        expect($array)
            ->toHaveKey('id', $conversation->id)
            ->toHaveKey('title', 'Test Conversation')
            ->toHaveKey('type', 'group')
            ->toHaveKey('created_by', $user->id)
            ->toHaveKey('last_message_at')
            ->toHaveKey('created_at')
            ->toHaveKey('updated_at')
            ->toHaveKey('participants')
            ->toHaveKey('participants_count', 2)
            ->toHaveKey('last_message')
            ->toHaveKey('unread_count')
            ->toHaveKey('current_user_joined_at')
            ->toHaveKey('is_muted', false)
            ->toHaveKey('is_pinned', false);

        expect($array['participants'])->toBeInstanceOf(Illuminate\Http\Resources\Json\AnonymousResourceCollection::class);
        expect($array['last_message'])->toBeInstanceOf(App\Http\Resources\MessageResource::class);
    });

    it('generates title from participants for direct conversations', function () {
        $user = User::factory()->create(['name' => 'John Doe']);
        $otherUser = User::factory()->create(['name' => 'Jane Smith']);
        $conversation = Conversation::factory()->direct()->create([
            'title' => null,
            'created_by' => $user->id,
        ]);

        $conversation->participants()->attach([
            $user->id => ['joined_at' => now(), 'is_admin' => true],
            $otherUser->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        $conversation->load('participants');

        $request = Request::create('/');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $resource = new ConversationResource($conversation);
        $array = $resource->toArray($request);

        expect($array['title'])->toBe('Jane Smith');
    });

    it('calculates unread count correctly', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $conversation = Conversation::factory()->create();

        $conversation->participants()->attach([
            $user->id => ['joined_at' => now(), 'is_admin' => true],
            $otherUser->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        // Create unread messages from other user
        Message::factory()->count(3)->unread()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $otherUser->id,
        ]);

        // Create read message from other user
        Message::factory()->read()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $otherUser->id,
        ]);

        // Create message from current user (shouldn't count as unread)
        Message::factory()->unread()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
        ]);

        $conversation->load('participants');

        $request = Request::create('/');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $resource = new ConversationResource($conversation);
        $array = $resource->toArray($request);

        expect($array['unread_count'])->toBe(3);
    });

    it('handles conversation without last message', function () {
        $user = User::factory()->create();
        $conversation = Conversation::factory()->create();

        $conversation->participants()->attach([
            $user->id => ['joined_at' => now(), 'is_admin' => true],
        ]);

        $conversation->load(['participants', 'messages' => function ($query) {
            $query->latest()->limit(1);
        }]);

        $request = Request::create('/');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $resource = new ConversationResource($conversation);
        $array = $resource->toArray($request);

        expect($array['last_message'])->toBeNull();
    });

    it('handles unauthenticated requests', function () {
        $user = User::factory()->create();
        $conversation = Conversation::factory()->create();

        $conversation->participants()->attach([
            $user->id => ['joined_at' => now(), 'is_admin' => true],
        ]);

        $conversation->load('participants');

        $request = Request::create('/');
        $request->setUserResolver(function () {
            return null;
        });

        $resource = new ConversationResource($conversation);
        $array = $resource->toArray($request);

        expect($array['unread_count'])->toBe(0);
        expect($array['current_user_joined_at'])->toBeNull();
    });

    it('handles conversation with multiple other participants', function () {
        $user = User::factory()->create(['name' => 'John Doe']);
        $user2 = User::factory()->create(['name' => 'Jane Smith']);
        $user3 = User::factory()->create(['name' => 'Bob Wilson']);
        $conversation = Conversation::factory()->group()->create([
            'title' => null,
            'created_by' => $user->id,
        ]);

        $conversation->participants()->attach([
            $user->id => ['joined_at' => now(), 'is_admin' => true],
            $user2->id => ['joined_at' => now(), 'is_admin' => false],
            $user3->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        $conversation->load('participants');

        $request = Request::create('/');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $resource = new ConversationResource($conversation);
        $array = $resource->toArray($request);

        expect($array['title'])->toBe('Jane Smith, Bob Wilson');
        expect($array['participants_count'])->toBe(3);
    });
});
