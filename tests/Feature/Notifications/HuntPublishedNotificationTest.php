<?php

declare(strict_types=1);

use App\Models\Hunt;
use App\Models\User;
use App\Notifications\HuntPublishedNotification;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    Notification::fake();
});

it('sends notification to followers when hunt is published', function () {
    $author = User::factory()->create();
    $follower1 = User::factory()->create();
    $follower2 = User::factory()->create();
    $nonFollower = User::factory()->create();

    // Create follow relationships
    $follower1->follow($author);
    $follower2->follow($author);

    actingAs($author);

    // Create a hunt
    $response = $this->post('/hunts', [
        'title' => 'My Awesome Hunt',
        'content' => 'This is an amazing hunt content!',
    ]);

    $response->assertRedirect('/hunts');

    // Assert notifications were sent to followers only
    Notification::assertSentTo(
        [$follower1, $follower2],
        HuntPublishedNotification::class,
        function ($notification, $channels, $notifiable) use ($author) {
            $data = $notification->toArray($notifiable);

            return $data['type'] === 'hunt_published' &&
                   $data['author']['id'] === $author->id &&
                   $data['title'] === 'Novo Hunt' &&
                   str_contains($data['message'], $author->name);
        }
    );

    // Assert notification was NOT sent to non-follower
    Notification::assertNotSentTo($nonFollower, HuntPublishedNotification::class);
});

it('includes hunt details in notification data', function () {
    $author = User::factory()->create();
    $follower = User::factory()->create();

    $follower->follow($author);

    // Create hunt directly
    $hunt = Hunt::factory()->create([
        'owner_id' => $author->id,
        'content' => 'Test hunt content goes here.',
    ]);

    // Create and test notification
    $notification = new HuntPublishedNotification($hunt, $author);
    $data = $notification->toArray($follower);

    expect($data['hunt']['id'])->toBe($hunt->id)
        ->and($data['hunt']['content_preview'])->toBe('Test hunt content goes here.')
        ->and($data['action_url'])->toContain('/hunts/');
});

it('uses database and broadcast channels by default', function () {
    $author = User::factory()->create();
    $follower = User::factory()->create(['notification_settings' => null]);

    $follower->follow($author);

    $hunt = Hunt::factory()->create(['owner_id' => $author->id]);

    $notification = new HuntPublishedNotification($hunt, $author);

    $channels = $notification->via($follower);

    expect($channels)->toContain('database')
        ->and($channels)->toContain('broadcast')
        ->and($channels)->not->toContain('mail'); // Email is opt-in
});

it('respects user notification settings for hunt notifications', function () {
    $author = User::factory()->create();
    $follower = User::factory()->create([
        'notification_settings' => [
            'hunt_notifications_in_app' => false,
            'hunt_notifications_browser' => false,
            'hunt_notifications_email' => false,
        ],
    ]);

    $follower->follow($author);

    $hunt = Hunt::factory()->create(['owner_id' => $author->id]);

    $notification = new HuntPublishedNotification($hunt, $author);

    $channels = $notification->via($follower);

    expect($channels)->toBeEmpty();
});

it('includes email channel when user opts in', function () {
    $author = User::factory()->create();
    $follower = User::factory()->create([
        'notification_settings' => [
            'hunt_notifications_in_app' => true,
            'hunt_notifications_browser' => true,
            'hunt_notifications_email' => true,
        ],
    ]);

    $follower->follow($author);

    $hunt = Hunt::factory()->create(['owner_id' => $author->id]);

    $notification = new HuntPublishedNotification($hunt, $author);

    $channels = $notification->via($follower);

    expect($channels)->toContain('database')
        ->and($channels)->toContain('broadcast')
        ->and($channels)->toContain('mail');
});

it('does not send notifications to users with pending follow requests', function () {
    $author = User::factory()->create();
    $pendingFollower = User::factory()->create();

    // Create a follow request but don't accept it
    $pendingFollower->follow($author);

    // Manually update the follow to be pending (not accepted)
    $pendingFollower->followings()
        ->where('followable_id', $author->id)
        ->update(['accepted_at' => null]);

    actingAs($author);

    $response = $this->post('/hunts', [
        'title' => 'My Hunt',
        'content' => 'Content here',
    ]);

    $response->assertRedirect('/hunts');

    // Should not send to pending follower
    Notification::assertNotSentTo($pendingFollower, HuntPublishedNotification::class);
});

it('notification includes author information', function () {
    $author = User::factory()->create([
        'name' => 'John Doe',
        'user_name' => 'johndoe',
    ]);
    $follower = User::factory()->create();

    $follower->follow($author);

    $hunt = Hunt::factory()->create(['owner_id' => $author->id]);

    $notification = new HuntPublishedNotification($hunt, $author);

    $broadcastData = $notification->toBroadcast($follower)->data;

    expect($broadcastData['author'])->toHaveKey('id', $author->id)
        ->and($broadcastData['author'])->toHaveKey('name', 'John Doe')
        ->and($broadcastData['author'])->toHaveKey('username', 'johndoe');
});

it('truncates content preview when content is longer than 100 characters', function () {
    $author = User::factory()->create();
    $follower = User::factory()->create();

    $follower->follow($author);

    $longContent = str_repeat('a', 150);
    $hunt = Hunt::factory()->create([
        'owner_id' => $author->id,
        'content' => $longContent,
    ]);

    $notification = new HuntPublishedNotification($hunt, $author);

    $mailMessage = $notification->toMail($follower);
    $broadcastData = $notification->toBroadcast($follower)->data;
    $arrayData = $notification->toArray($follower);

    expect($broadcastData['hunt']['content_preview'])->toBe(mb_substr($longContent, 0, 100).'...')
        ->and($arrayData['hunt']['content_preview'])->toBe(mb_substr($longContent, 0, 100).'...')
        ->and($broadcastData['hunt']['content_preview'])->toHaveLength(103); // 100 + '...'
});

it('does not truncate content preview when content is 100 characters or less', function () {
    $author = User::factory()->create();
    $follower = User::factory()->create();

    $follower->follow($author);

    $shortContent = str_repeat('a', 50);
    $hunt = Hunt::factory()->create([
        'owner_id' => $author->id,
        'content' => $shortContent,
    ]);

    $notification = new HuntPublishedNotification($hunt, $author);

    $mailMessage = $notification->toMail($follower);
    $broadcastData = $notification->toBroadcast($follower)->data;
    $arrayData = $notification->toArray($follower);

    expect($broadcastData['hunt']['content_preview'])->toBe($shortContent)
        ->and($arrayData['hunt']['content_preview'])->toBe($shortContent)
        ->and($broadcastData['hunt']['content_preview'])->toHaveLength(50);
});

it('handles exactly 100 character content without truncation', function () {
    $author = User::factory()->create();
    $follower = User::factory()->create();

    $follower->follow($author);

    $exactContent = str_repeat('a', 100);
    $hunt = Hunt::factory()->create([
        'owner_id' => $author->id,
        'content' => $exactContent,
    ]);

    $notification = new HuntPublishedNotification($hunt, $author);

    $broadcastData = $notification->toBroadcast($follower)->data;
    $arrayData = $notification->toArray($follower);

    expect($broadcastData['hunt']['content_preview'])->toBe($exactContent)
        ->and($arrayData['hunt']['content_preview'])->toBe($exactContent)
        ->and($broadcastData['hunt']['content_preview'])->toHaveLength(100);
});
