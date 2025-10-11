<?php

declare(strict_types=1);

use App\Actions\Chat\ValidateUserIsParticipantAction;
use App\Models\Conversation;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    $this->action = new ValidateUserIsParticipantAction();
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

describe('ValidateUserIsParticipantAction', function () {
    it('returns conversation when user is a participant', function () {
        $conversation = Conversation::factory()->direct()->create();
        $conversation->participants()->attach([
            $this->user->id => ['joined_at' => now(), 'is_admin' => true],
            $this->otherUser->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        $result = $this->action->handle($this->user, $conversation->id);

        expect($result)->toBeInstanceOf(Conversation::class)
            ->and($result->id)->toBe($conversation->id);
    });

    it('throws HttpException with 403 status when user is not a participant', function () {
        $conversation = Conversation::factory()->direct()->create();
        $conversation->participants()->attach([
            $this->otherUser->id => ['joined_at' => now(), 'is_admin' => true],
        ]);

        $this->action->handle($this->user, $conversation->id);
    })->throws(HttpException::class, 'You are not authorized to access this conversation');

    it('throws HttpException with 403 status when conversation does not exist', function () {
        $this->action->handle($this->user, 99999);
    })->throws(HttpException::class, 'You are not authorized to access this conversation');

    it('validates correct participant in group conversation', function () {
        $thirdUser = User::factory()->create();

        $conversation = Conversation::factory()->create(['type' => 'group', 'title' => 'Test Group']);
        $conversation->participants()->attach([
            $this->user->id => ['joined_at' => now(), 'is_admin' => true],
            $this->otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            $thirdUser->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        $result = $this->action->handle($this->user, $conversation->id);

        expect($result)->toBeInstanceOf(Conversation::class)
            ->and($result->id)->toBe($conversation->id)
            ->and($result->type)->toBe('group');
    });

    it('throws exception when checking wrong user in group conversation', function () {
        $nonParticipant = User::factory()->create();
        $conversation = Conversation::factory()->create(['type' => 'group', 'title' => 'Test Group']);
        $conversation->participants()->attach([
            $this->user->id => ['joined_at' => now(), 'is_admin' => true],
            $this->otherUser->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        $this->action->handle($nonParticipant, $conversation->id);
    })->throws(HttpException::class);

    it('returns conversation with correct participants relationship loaded', function () {
        $conversation = Conversation::factory()->direct()->create();
        $conversation->participants()->attach([
            $this->user->id => ['joined_at' => now(), 'is_admin' => true],
            $this->otherUser->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        $result = $this->action->handle($this->user, $conversation->id);

        expect($result)->toBeInstanceOf(Conversation::class)
            ->and($result->relationLoaded('participants'))->toBeFalse();
    });
});
