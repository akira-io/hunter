<?php

declare(strict_types=1);

use App\Models\AcademicBackground;
use App\Models\Conversation;
use App\Models\Hunt;
use App\Models\Message;
use App\Models\User;

test('to array', function () {
    $user = User::factory()->create()->refresh();

    expect(array_keys($user->toArray()))
        ->toBe([
            'id',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
            'user_name',
            'avatar_url',
            'location',
            'bio',
            'github_id',
            'github_token',
            'github_refresh_token',
            'skills',
            'github_url',
            'twitter_url',
            'linkedin_url',
            'bluesky_url',
            'website_url',
            'youtube_url',
            'notification_settings',
            'onboarding_completed',
            'onboarding_completed_at',
            'google_id',
            'google_token',
            'google_refresh_token',
            'privacy_settings',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'two_factor_confirmed_at',
        ]);
});

it('should has many academic backgrounds', function () {
    $user = User::factory()->create();
    $user->academicBackgrounds()->createMany(
        AcademicBackground::factory()->count(3)->make()->toArray()
    );

    expect($user->academicBackgrounds)
        ->each
        ->toBeInstanceOf(AcademicBackground::class)
        ->and($user->academicBackgrounds)
        ->toHaveCount(3);
});

it('should has many hunts', function () {
    $user = User::factory()->create();

    $user->hunts()->createMany(
        Hunt::factory()->count(3)->make()->toArray()
    );

    expect($user->hunts)
        ->each
        ->toBeInstanceOf(Hunt::class)
        ->and($user->hunts)
        ->toHaveCount(3);
});

it('should has many messages', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();

    $user->messages()->createMany([
        ['conversation_id' => $conversation->id, 'content' => 'Test message 1', 'type' => 'text'],
        ['conversation_id' => $conversation->id, 'content' => 'Test message 2', 'type' => 'text'],
    ]);

    expect($user->messages)
        ->each
        ->toBeInstanceOf(Message::class)
        ->and($user->messages)
        ->toHaveCount(2);
});

it('should has many created conversations', function () {
    $user = User::factory()->create();

    $user->createdConversations()->createMany([
        ['title' => 'Conversation 1', 'type' => 'group'],
        ['title' => 'Conversation 2', 'type' => 'group'],
    ]);

    expect($user->createdConversations)
        ->each
        ->toBeInstanceOf(Conversation::class)
        ->and($user->createdConversations)
        ->toHaveCount(2);
});

it('should handle non-scalar avatar_url values', function () {
    $user = User::factory()->create();

    // Test with array (non-scalar)
    $user->avatar_url = ['not', 'a', 'string'];
    expect($user->getAvatarUrlAttribute($user->avatar_url))->toBeNull();

    // Test with object (non-scalar)
    $user->avatar_url = (object) ['not' => 'a string'];
    expect($user->getAvatarUrlAttribute($user->avatar_url))->toBeNull();
});

it('should convert relative paths to absolute URLs', function () {
    $user = User::factory()->create();

    // Test with relative path that should be converted to absolute URL
    $relativePath = '/images/avatar.jpg';
    $result = $user->getAvatarUrlAttribute($relativePath);

    expect($result)->toContain('/images/avatar.jpg')
        ->and($result)->toStartWith('http');
});

it('returns searchable array with correct structure', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'location' => 'New York',
        'user_name' => 'johndoe',
        'skills' => [
            ['value' => 'PHP', 'label' => 'PHP'],
            ['value' => 'Laravel', 'label' => 'Laravel'],
        ],
    ]);

    $searchableArray = $user->toSearchableArray();

    expect($searchableArray)
        ->toHaveKey('id')
        ->toHaveKey('name')
        ->toHaveKey('email')
        ->toHaveKey('location')
        ->toHaveKey('user_name')
        ->toHaveKey('skills')
        ->and($searchableArray['name'])->toBe('John Doe')
        ->and($searchableArray['email'])->toBe('john@example.com')
        ->and($searchableArray['location'])->toBe('New York')
        ->and($searchableArray['user_name'])->toBe('johndoe')
        ->and($searchableArray['skills'])->toBeArray();
});

it('can receive messages from another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    expect($user1->canReceiveMessagesFrom($user2))->toBeTrue();
});

it('cannot receive messages from null sender', function () {
    $user = User::factory()->create();

    expect($user->canReceiveMessagesFrom(null))->toBeFalse();
});

it('cannot receive messages from itself', function () {
    $user = User::factory()->create();

    expect($user->canReceiveMessagesFrom($user))->toBeFalse();
});

it('cannot receive messages from blocked user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user1->block($user2);

    expect($user1->canReceiveMessagesFrom($user2))->toBeFalse();
});

it('cannot receive messages when blocked by sender', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user2->block($user1); // user2 blocks user1

    expect($user1->canReceiveMessagesFrom($user2))->toBeFalse();
});
