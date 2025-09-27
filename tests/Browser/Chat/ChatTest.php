 <?php

declare(strict_types=1);

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

use function Pest\Laravel\actingAs;

describe('Chat Browser Tests', function () {
    beforeEach(function () {
        $this->user1 = User::factory()->create([
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
        ]);

        $this->user2 = User::factory()->create([
            'name' => 'Bob Johnson',
            'email' => 'bob@example.com',
        ]);

        $this->user3 = User::factory()->create([
            'name' => 'Charlie Brown',
            'email' => 'charlie@example.com',
        ]);
    });

    it('can open and interact with chat window', function () {
        actingAs($this->user1);

        // Create existing conversation
        $conversation = Conversation::factory()->direct()->create();
        $conversation->participants()->attach([
            $this->user1->id => ['joined_at' => now(), 'is_admin' => true],
            $this->user2->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        // Add some existing messages
        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user2->id,
            'content' => 'Hey Alice!',
            'created_at' => now()->subMinutes(10),
        ]);

        $page = visit('/');

        $page->assertSee('Alice Smith') // User should see their name
            ->click('[data-testid="chat-users-button"]') // Open chat users panel
            ->assertSee('Bob Johnson') // Should see other user
            ->click('Bob Johnson') // Click on user to start chat
            ->waitForText('Hey Alice!') // Wait for existing message to load
            ->assertSee('Hey Alice!')
            ->type('[data-testid="message-input"]', 'Hi Bob! How are you?')
            ->click('[data-testid="send-message-button"]')
            ->waitForText('Hi Bob! How are you?') // Wait for message to appear
            ->assertSee('Hi Bob! How are you?');

        // Verify message was saved to database
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'user_id' => $this->user1->id,
            'content' => 'Hi Bob! How are you?',
        ]);
    });

    it('shows unread message indicators', function () {
        actingAs($this->user1);

        // Create conversation with unread messages
        $conversation = Conversation::factory()->direct()->create();
        $conversation->participants()->attach([
            $this->user1->id => ['joined_at' => now(), 'is_admin' => true],
            $this->user2->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        // Create unread messages from user2
        Message::factory()->count(3)->unread()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user2->id,
        ]);

        $page = visit('/');

        $page->click('[data-testid="chat-users-button"]')
            ->assertSee('3') // Should show unread count badge
            ->assertSee('Bob Johnson')
            ->click('Bob Johnson')
            ->waitFor('[data-testid="chat-window"]');

        // After opening chat, unread count should decrease/disappear
        $page->pause(1000) // Wait for mark as read API call
            ->click('[data-testid="chat-users-button"]')
            ->assertDontSee('3'); // Unread badge should be gone
    });

    it('can minimize and maximize chat windows', function () {
        actingAs($this->user1);

        $conversation = Conversation::factory()->direct()->create();
        $conversation->participants()->attach([
            $this->user1->id => ['joined_at' => now(), 'is_admin' => true],
            $this->user2->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        $page = visit('/');

        $page->click('[data-testid="chat-users-button"]')
            ->click('Bob Johnson')
            ->waitFor('[data-testid="chat-window"]')
            ->assertVisible('[data-testid="message-input"]') // Chat should be expanded
            ->click('[data-testid="minimize-chat-button"]')
            ->assertNotVisible('[data-testid="message-input"]') // Chat should be minimized
            ->click('[data-testid="chat-header"]') // Click header to maximize
            ->assertVisible('[data-testid="message-input"]'); // Chat should be expanded again
    });

    it('can close chat windows', function () {
        actingAs($this->user1);

        $conversation = Conversation::factory()->direct()->create();
        $conversation->participants()->attach([
            $this->user1->id => ['joined_at' => now(), 'is_admin' => true],
            $this->user2->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        $page = visit('/');

        $page->click('[data-testid="chat-users-button"]')
            ->click('Bob Johnson')
            ->waitFor('[data-testid="chat-window"]')
            ->click('[data-testid="close-chat-button"]')
            ->assertNotVisible('[data-testid="chat-window"]'); // Chat window should be closed
    });

    it('handles multiple chat windows with background tabs', function () {
        actingAs($this->user1);

        // Create conversations with multiple users
        $conversation1 = Conversation::factory()->direct()->create();
        $conversation1->participants()->attach([
            $this->user1->id => ['joined_at' => now(), 'is_admin' => true],
            $this->user2->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        $conversation2 = Conversation::factory()->direct()->create();
        $conversation2->participants()->attach([
            $this->user1->id => ['joined_at' => now(), 'is_admin' => true],
            $this->user3->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        $page = visit('/');

        $page->click('[data-testid="chat-users-button"]')
            ->click('Bob Johnson') // Open first chat
            ->waitFor('[data-testid="chat-window"]')
            ->click('[data-testid="chat-users-button"]')
            ->click('Charlie Brown') // Open second chat
            ->waitFor('[data-testid="background-chat-tab"]') // First chat should move to background
            ->assertSee('Bob Johnson') // Should see background tab
            ->click('Bob Johnson') // Click background tab to switch
            ->waitFor('[data-testid="chat-window"]')
            ->assertSee('Charlie Brown'); // Charlie should now be in background
    });

    it('shows online status indicators', function () {
        actingAs($this->user1);

        // Mock user2 as online (this would normally be handled by presence system)
        cache()->put("user_online_{$this->user2->id}", now(), now()->addMinutes(10));

        $page = visit('/');

        $page->click('[data-testid="chat-users-button"]')
            ->assertSee('Bob Johnson')
            ->assertSee('Online') // Should show online status
            ->assertVisible('[data-testid="online-indicator"]'); // Should show green dot
    });

    it('can send different types of messages', function () {
        actingAs($this->user1);

        $conversation = Conversation::factory()->direct()->create();
        $conversation->participants()->attach([
            $this->user1->id => ['joined_at' => now(), 'is_admin' => true],
            $this->user2->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        $page = visit('/');

        $page->click('[data-testid="chat-users-button"]')
            ->click('Bob Johnson')
            ->waitFor('[data-testid="chat-window"]')
            // Send text message
            ->type('[data-testid="message-input"]', 'Hello Bob!')
            ->click('[data-testid="send-message-button"]')
            ->waitForText('Hello Bob!')
            ->assertSee('Hello Bob!');

        // TODO: Add tests for image/file uploads when implemented in UI
    });

    it('shows proper error handling for failed messages', function () {
        actingAs($this->user1);

        $page = visit('/');

        // Try to send message without opening a chat (should not work)
        $page->click('[data-testid="chat-users-button"]')
            ->assertDontSee('[data-testid="message-input"]'); // No input should be visible without active chat
    });

    it('handles real-time message updates', function () {
        actingAs($this->user1);

        $conversation = Conversation::factory()->direct()->create();
        $conversation->participants()->attach([
            $this->user1->id => ['joined_at' => now(), 'is_admin' => true],
            $this->user2->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        $page = visit('/');

        $page->click('[data-testid="chat-users-button"]')
            ->click('Bob Johnson')
            ->waitFor('[data-testid="chat-window"]');

        // Simulate receiving a message from another user via API
        // (In real scenario, this would come through websockets)
        $newMessage = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user2->id,
            'content' => 'Message from Bob!',
        ]);

        // Refresh page to simulate real-time update
        $page->refresh()
            ->waitFor('[data-testid="chat-window"]')
            ->waitForText('Message from Bob!')
            ->assertSee('Message from Bob!');
    });

    it('redirects to mobile chat on small screens', function () {
        actingAs($this->user1);

        $conversation = Conversation::factory()->direct()->create();
        $conversation->participants()->attach([
            $this->user1->id => ['joined_at' => now(), 'is_admin' => true],
            $this->user2->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        // Simulate mobile viewport
        $page = visit('/', [
            'viewport' => ['width' => 375, 'height' => 667], // iPhone size
        ]);

        $page->click('[data-testid="chat-users-button"]')
            ->click('Bob Johnson')
            ->waitForUrl("/chat/mobile/{$conversation->id}") // Should redirect to mobile chat
            ->assertSee('Bob Johnson') // Should show conversation title
            ->assertVisible('[data-testid="mobile-chat-back-button"]'); // Should show back button
    });

    it('shows proper conversation titles and avatars', function () {
        actingAs($this->user1);

        // Create group conversation
        $groupConversation = Conversation::factory()->group()->create([
            'title' => 'Project Team',
        ]);
        $groupConversation->participants()->attach([
            $this->user1->id => ['joined_at' => now(), 'is_admin' => true],
            $this->user2->id => ['joined_at' => now(), 'is_admin' => false],
            $this->user3->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        // Create direct conversation
        $directConversation = Conversation::factory()->direct()->create();
        $directConversation->participants()->attach([
            $this->user1->id => ['joined_at' => now(), 'is_admin' => true],
            $this->user2->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        $page = visit('/');

        $page->click('[data-testid="chat-users-button"]')
            ->assertSee('Bob Johnson') // Direct conversation should show user name
            ->assertSee('Project Team'); // Group conversation should show title

        // Check that avatars are displayed (even if they're fallback icons)
        $page->assertVisible('[data-testid="user-avatar"]');
    });
})->group('browser');
