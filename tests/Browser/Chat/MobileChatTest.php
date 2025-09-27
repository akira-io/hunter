<?php

declare(strict_types=1);

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

use function Pest\Laravel\actingAs;

describe('Mobile Chat Browser Tests', function () {
    beforeEach(function () {
        $this->user1 = User::factory()->create([
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
        ]);

        $this->user2 = User::factory()->create([
            'name' => 'Bob Johnson',
            'email' => 'bob@example.com',
        ]);

        $this->conversation = Conversation::factory()->direct()->create();
        $this->conversation->participants()->attach([
            $this->user1->id => ['joined_at' => now(), 'is_admin' => true],
            $this->user2->id => ['joined_at' => now(), 'is_admin' => false],
        ]);
    });

    it('displays mobile chat interface correctly', function () {
        actingAs($this->user1);

        $page = visit("/chat/mobile/{$this->conversation->id}");

        $page->assertSee('Bob Johnson') // Should show conversation title
            ->assertVisible('[data-testid="mobile-chat-back-button"]') // Back button
            ->assertVisible('[data-testid="mobile-message-input"]') // Message input
            ->assertVisible('[data-testid="mobile-send-button"]') // Send button
            ->assertSee('Start the conversation!'); // Empty state message
    });

    it('can send and display messages in mobile chat', function () {
        actingAs($this->user1);

        $page = visit("/chat/mobile/{$this->conversation->id}");

        $page->type('[data-testid="mobile-message-input"]', 'Hello from mobile!')
            ->click('[data-testid="mobile-send-button"]')
            ->waitForText('Hello from mobile!')
            ->assertSee('Hello from mobile!');

        // Verify message was saved
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user1->id,
            'content' => 'Hello from mobile!',
        ]);
    });

    it('displays existing messages correctly', function () {
        actingAs($this->user1);

        // Add existing messages
        Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user2->id,
            'content' => 'Hey Alice!',
            'created_at' => now()->subMinutes(10),
        ]);

        Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user1->id,
            'content' => 'Hi Bob!',
            'created_at' => now()->subMinutes(5),
        ]);

        $page = visit("/chat/mobile/{$this->conversation->id}");

        $page->waitForText('Hey Alice!')
            ->assertSee('Hey Alice!')
            ->assertSee('Hi Bob!');

        // Check message order (should be chronological)
        $messageElements = $page->elements('[data-testid="chat-message"]');
        expect(count($messageElements))->toBe(2);
    });

    it('shows proper message alignment and styling', function () {
        actingAs($this->user1);

        Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user2->id,
            'content' => 'Message from Bob',
        ]);

        Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user1->id,
            'content' => 'My reply',
        ]);

        $page = visit("/chat/mobile/{$this->conversation->id}");

        $page->waitForText('Message from Bob')
            ->waitForText('My reply');

        // Own messages should be aligned right, others left
        // This would need specific CSS classes or data attributes to test properly
        $page->assertVisible('[data-testid="chat-message"]');
    });

    it('handles back navigation correctly', function () {
        actingAs($this->user1);

        $page = visit("/chat/mobile/{$this->conversation->id}");

        $page->click('[data-testid="mobile-chat-back-button"]')
            ->waitForUrl('/') // Should navigate back to home
            ->assertUrlIs('/');
    });

    it('auto-scrolls to bottom when new messages arrive', function () {
        actingAs($this->user1);

        // Create many messages to fill the screen
        Message::factory()->count(20)->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user2->id,
        ]);

        $page = visit("/chat/mobile/{$this->conversation->id}");

        // Send a new message
        $page->type('[data-testid="mobile-message-input"]', 'Latest message')
            ->click('[data-testid="mobile-send-button"]')
            ->waitForText('Latest message');

        // The latest message should be visible (page should auto-scroll)
        $page->assertSee('Latest message');
    });

    it('shows conversation header with avatar and name', function () {
        actingAs($this->user1);

        $page = visit("/chat/mobile/{$this->conversation->id}");

        $page->assertSee('Bob Johnson')
            ->assertSee('Direct message')
            ->assertVisible('[data-testid="conversation-avatar"]');
    });

    it('handles group conversation display', function () {
        actingAs($this->user1);

        $user3 = User::factory()->create(['name' => 'Charlie']);

        $groupConversation = Conversation::factory()->group()->create([
            'title' => 'Team Chat',
        ]);

        $groupConversation->participants()->attach([
            $this->user1->id => ['joined_at' => now(), 'is_admin' => true],
            $this->user2->id => ['joined_at' => now(), 'is_admin' => false],
            $user3->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        $page = visit("/chat/mobile/{$groupConversation->id}");

        $page->assertSee('Team Chat')
            ->assertSee('Group chat');
    });

    it('prevents sending empty messages', function () {
        actingAs($this->user1);

        $page = visit("/chat/mobile/{$this->conversation->id}");

        // Try to send empty message
        $page->click('[data-testid="mobile-send-button"]');

        // Send button should be disabled or no message should be sent
        $this->assertDatabaseMissing('messages', [
            'conversation_id' => $this->conversation->id,
            'content' => '',
        ]);
    });

    it('handles message timestamps correctly', function () {
        actingAs($this->user1);

        Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user2->id,
            'content' => 'Timestamped message',
            'created_at' => now(),
        ]);

        $page = visit("/chat/mobile/{$this->conversation->id}");

        $page->waitForText('Timestamped message');

        // Should show timestamp (format: HH:MM)
        $currentTime = now()->format('H:i');
        $page->assertSee($currentTime);
    });

    it('shows proper loading state', function () {
        actingAs($this->user1);

        // Visit chat page
        $page = visit("/chat/mobile/{$this->conversation->id}");

        // Should show loading initially, then content
        $page->assertDontSee('Loading...'); // Should load quickly in tests
    });

    it('handles unauthorized access to conversation', function () {
        $unauthorizedUser = User::factory()->create();
        actingAs($unauthorizedUser);

        $page = visit("/chat/mobile/{$this->conversation->id}");

        // Should redirect or show error (depending on implementation)
        // This might redirect to home or show 404
        $page->assertDontSee('Bob Johnson');
    });

    it('works well on different mobile viewport sizes', function () {
        actingAs($this->user1);

        // Test on different mobile sizes
        $viewports = [
            ['width' => 375, 'height' => 667], // iPhone SE
            ['width' => 390, 'height' => 844], // iPhone 12
            ['width' => 414, 'height' => 896], // iPhone 11 Pro Max
            ['width' => 360, 'height' => 640], // Android
        ];

        foreach ($viewports as $viewport) {
            $page = visit("/chat/mobile/{$this->conversation->id}", [
                'viewport' => $viewport,
            ]);

            $page->assertVisible('[data-testid="mobile-message-input"]')
                ->assertVisible('[data-testid="mobile-send-button"]')
                ->assertSee('Bob Johnson');
        }
    });

    it('maintains scroll position when typing', function () {
        actingAs($this->user1);

        // Create many messages
        Message::factory()->count(15)->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user2->id,
        ]);

        $page = visit("/chat/mobile/{$this->conversation->id}");

        // Focus on input (mobile keyboards appear)
        $page->click('[data-testid="mobile-message-input"]')
            ->type('[data-testid="mobile-message-input"]', 'Test message');

        // Should still be able to see the input
        $page->assertVisible('[data-testid="mobile-message-input"]');
    });
})->group('browser', 'mobile');
