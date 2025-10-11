<?php

declare(strict_types=1);

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it can be created with factory data', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();

    $participant = new ConversationParticipant([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'joined_at' => now(),
        'last_read_at' => now(),
        'is_admin' => true,
    ]);

    expect($participant->conversation_id)->toBe($conversation->id)
        ->and($participant->user_id)->toBe($user->id)
        ->and($participant->is_admin)->toBeTrue();
});

test('it belongs to a conversation', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();

    $participant = new ConversationParticipant([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'joined_at' => now(),
        'is_admin' => false,
    ]);

    $participant->save();

    expect($participant->conversation)->toBeInstanceOf(Conversation::class)
        ->and($participant->conversation->id)->toBe($conversation->id);
});

test('it belongs to a user', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();

    $participant = new ConversationParticipant([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'joined_at' => now(),
        'is_admin' => false,
    ]);

    $participant->save();

    expect($participant->user)->toBeInstanceOf(User::class)
        ->and($participant->user->id)->toBe($user->id);
});

test('it can update last read timestamp', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();

    $participant = new ConversationParticipant([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'joined_at' => now(),
        'last_read_at' => null,
        'is_admin' => false,
    ]);

    $participant->save();

    expect($participant->last_read_at)->toBeNull();

    $participant->updateLastRead();
    $participant->refresh();

    expect($participant->last_read_at)->not->toBeNull();
});

test('it casts attributes correctly', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();
    $joinedAt = now()->subDays(2);
    $lastReadAt = now()->subHours(1);

    $participant = new ConversationParticipant([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'joined_at' => $joinedAt,
        'last_read_at' => $lastReadAt,
        'is_admin' => true,
    ]);

    $participant->save();
    $participant->refresh();

    expect($participant->joined_at)->toBeInstanceOf(Carbon\CarbonInterface::class)
        ->and($participant->last_read_at)->toBeInstanceOf(Carbon\CarbonInterface::class)
        ->and($participant->is_admin)->toBeBool()
        ->and($participant->is_admin)->toBeTrue();
});

test('it handles boolean casting for is_admin', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();

    $participant = new ConversationParticipant([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'joined_at' => now(),
        'is_admin' => 0, // integer 0
    ]);

    $participant->save();
    $participant->refresh();

    expect($participant->is_admin)->toBeBool()
        ->and($participant->is_admin)->toBeFalse();
});
