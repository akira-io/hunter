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

    expect($result)->toContain('localhost')
        ->and($result)->toContain('/images/avatar.jpg')
        ->and($result)->toStartWith('http');
});
