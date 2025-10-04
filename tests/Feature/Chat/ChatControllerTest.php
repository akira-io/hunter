<?php

declare(strict_types=1);

use App\Models\Conversation;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('ChatController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        actingAs($this->user);
    });

    it('renders chat index page', function () {
        $response = get(route('chat.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('chat/index')
                ->has('currentUser', fn ($user) => $user
                    ->where('id', $this->user->id)
                    ->where('name', $this->user->name)
                    ->has('avatar_url')
                )
            );
    });

    it('renders chat show page with conversation', function () {
        $otherUser = User::factory()->create();
        $conversation = Conversation::factory()->create();
        $conversation->participants()->attach([
            $this->user->id => ['joined_at' => now(), 'is_admin' => true],
            $otherUser->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        $response = get(route('chat.show', ['conversation' => $conversation->id]));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('chat/desktop')
                ->where('conversationId', $conversation->id)
                ->has('currentUser', fn ($user) => $user
                    ->where('id', $this->user->id)
                    ->where('name', $this->user->name)
                    ->has('avatar_url')
                )
            );
    });

    it('renders chat show page even if conversation does not exist yet', function () {
        // Controller doesn't validate if conversation exists - API will handle that
        $nonExistentId = 99999;

        $response = get(route('chat.show', ['conversation' => $nonExistentId]));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('chat/desktop')
                ->where('conversationId', $nonExistentId)
            );
    });

    it('requires authentication for chat index', function () {
        auth()->logout();

        $response = get(route('chat.index'));

        $response->assertRedirect(route('login'));
    });

    it('requires authentication for chat show', function () {
        auth()->logout();

        $response = get(route('chat.show', ['conversation' => 1]));

        $response->assertRedirect(route('login'));
    });

    it('includes user avatar in currentUser data', function () {
        $response = get(route('chat.index'));

        $response->assertInertia(fn ($page) => $page
            ->has('currentUser.avatar_url')
        );
    });

    it('passes correct conversation id as integer', function () {
        $response = get(route('chat.show', ['conversation' => '123']));

        $response->assertInertia(fn ($page) => $page
            ->where('conversationId', 123) // Should be integer
        );
    });
});
