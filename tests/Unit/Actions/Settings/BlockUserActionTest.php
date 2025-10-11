<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Settings;

use App\Actions\Settings\BlockUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

final class BlockUserActionTest extends TestCase
{
    use RefreshDatabase;

    private BlockUserAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new BlockUserAction();
    }

    public function test_successfully_blocks_another_user(): void
    {
        $blocker = User::factory()->create();
        $userToBlock = User::factory()->create();

        $this->action->handle($blocker, $userToBlock->id);

        $this->assertTrue($blocker->hasBlocked($userToBlock));
    }

    public function test_cannot_block_yourself(): void
    {
        $user = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Não pode bloquear-se a si mesmo.');

        $this->action->handle($user, $user->id);
    }

    public function test_throws_exception_when_user_not_found(): void
    {
        $blocker = User::factory()->create();
        $nonExistentUserId = 99999;

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->action->handle($blocker, $nonExistentUserId);
    }

    public function test_can_block_multiple_users(): void
    {
        $blocker = User::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $this->action->handle($blocker, $user1->id);
        $this->action->handle($blocker, $user2->id);
        $this->action->handle($blocker, $user3->id);

        $this->assertTrue($blocker->hasBlocked($user1));
        $this->assertTrue($blocker->hasBlocked($user2));
        $this->assertTrue($blocker->hasBlocked($user3));
    }

    public function test_blocking_creates_database_record(): void
    {
        $blocker = User::factory()->create();
        $userToBlock = User::factory()->create();

        $this->action->handle($blocker, $userToBlock->id);

        $this->assertDatabaseHas('blocked_users', [
            'blocker_id' => $blocker->id,
            'blocked_id' => $userToBlock->id,
        ]);
    }

    public function test_idempotent_blocking_same_user_twice(): void
    {
        $blocker = User::factory()->create();
        $userToBlock = User::factory()->create();

        $this->action->handle($blocker, $userToBlock->id);
        $this->action->handle($blocker, $userToBlock->id);

        $this->assertTrue($blocker->hasBlocked($userToBlock));

        // Should only have one record
        $this->assertCount(1, $blocker->blockedUsers);
    }
}
