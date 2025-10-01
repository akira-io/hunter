<?php

declare(strict_types=1);

use App\Actions\Chat\CreateConversationAction;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

describe('CreateConversationAction', function () {
    beforeEach(function () {
        Event::fake();
        $this->action = new CreateConversationAction();
        $this->creator = User::factory()->create();
    });

    it('throws exception when direct conversation participant does not exist', function () {
        // Create a user and then delete it to simulate non-existent participant
        $tempUser = User::factory()->create();
        $nonExistentId = $tempUser->id;
        $tempUser->delete();

        expect(function () use ($nonExistentId) {
            $this->action->handle($this->creator, 'direct', [$nonExistentId]);
        })->toThrow(Exception::class, 'Invalid participant');
    });

    it('creates direct conversation successfully', function () {
        $otherUser = User::factory()->create();

        $result = $this->action->handle($this->creator, 'direct', [$otherUser->id]);

        expect($result)->toHaveKeys(['id', 'message', 'existing'])
            ->and($result['existing'])->toBeFalse()
            ->and($result['message'])->toBe('Conversation created successfully');

        $conversation = Conversation::find($result['id']);
        expect($conversation)->not->toBeNull()
            ->and($conversation->type)->toBe('direct')
            ->and($conversation->participants)->toHaveCount(2);
    });

    it('returns existing direct conversation when it already exists', function () {
        $otherUser = User::factory()->create();

        // Create first conversation
        $firstResult = $this->action->handle($this->creator, 'direct', [$otherUser->id]);

        // Try to create the same conversation again
        $secondResult = $this->action->handle($this->creator, 'direct', [$otherUser->id]);

        expect($secondResult)->toHaveKeys(['id', 'message', 'existing'])
            ->and($secondResult['existing'])->toBeTrue()
            ->and($secondResult['message'])->toBe('Conversation already exists')
            ->and($secondResult['id'])->toBe($firstResult['id']);
    });

    it('creates group conversation successfully', function () {
        $participant1 = User::factory()->create();
        $participant2 = User::factory()->create();

        $result = $this->action->handle($this->creator, 'group', [$participant1->id, $participant2->id], 'Test Group');

        expect($result)->toHaveKeys(['id', 'message', 'existing'])
            ->and($result['existing'])->toBeFalse()
            ->and($result['message'])->toBe('Conversation created successfully');

        $conversation = Conversation::find($result['id']);
        expect($conversation)->not->toBeNull()
            ->and($conversation->type)->toBe('group')
            ->and($conversation->title)->toBe('Test Group')
            ->and($conversation->participants)->toHaveCount(3);
    });

    it('throws exception when direct conversation has wrong number of participants', function () {
        $participant1 = User::factory()->create();
        $participant2 = User::factory()->create();

        expect(function () use ($participant1, $participant2) {
            $this->action->handle($this->creator, 'direct', [$participant1->id, $participant2->id]);
        })->toThrow(Exception::class, 'Direct conversations must have exactly one other participant');
    });

    it('throws exception when direct conversation has no participants', function () {
        expect(function () {
            $this->action->handle($this->creator, 'direct', []);
        })->toThrow(Exception::class, 'Direct conversations must have exactly one other participant');
    });

    it('filters out creator from participants list', function () {
        $otherUser = User::factory()->create();

        // Include creator's ID in participants - should be filtered out
        $result = $this->action->handle($this->creator, 'direct', [$this->creator->id, $otherUser->id]);

        $conversation = Conversation::find($result['id']);
        expect($conversation->participants)->toHaveCount(2); // Creator + otherUser only
    });

    it('sets creator as admin and others as non-admin', function () {
        $otherUser = User::factory()->create();

        $result = $this->action->handle($this->creator, 'direct', [$otherUser->id]);

        $conversation = Conversation::with('participants')->find($result['id']);

        $creatorParticipant = $conversation->participants->where('id', $this->creator->id)->first();
        $otherParticipant = $conversation->participants->where('id', $otherUser->id)->first();

        expect($creatorParticipant->pivot->is_admin)->toBe(1)
            ->and($otherParticipant->pivot->is_admin)->toBe(0);
    });
});
