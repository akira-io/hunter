<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\BlockedUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BlockedUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocker_relationship_returns_user_who_blocked(): void
    {
        $blocker = User::factory()->create(['name' => 'Blocker User']);
        $blocked = User::factory()->create(['name' => 'Blocked User']);

        $blockedUser = BlockedUser::create([
            'blocker_id' => $blocker->id,
            'blocked_id' => $blocked->id,
        ]);

        $this->assertInstanceOf(User::class, $blockedUser->blocker);
        $this->assertEquals($blocker->id, $blockedUser->blocker->id);
        $this->assertEquals('Blocker User', $blockedUser->blocker->name);
    }

    public function test_blocked_relationship_returns_user_who_was_blocked(): void
    {
        $blocker = User::factory()->create(['name' => 'Blocker User']);
        $blocked = User::factory()->create(['name' => 'Blocked User']);

        $blockedUser = BlockedUser::create([
            'blocker_id' => $blocker->id,
            'blocked_id' => $blocked->id,
        ]);

        $this->assertInstanceOf(User::class, $blockedUser->blocked);
        $this->assertEquals($blocked->id, $blockedUser->blocked->id);
        $this->assertEquals('Blocked User', $blockedUser->blocked->name);
    }

    public function test_can_create_blocked_user_with_fillable_attributes(): void
    {
        $blocker = User::factory()->create();
        $blocked = User::factory()->create();

        $blockedUser = BlockedUser::create([
            'blocker_id' => $blocker->id,
            'blocked_id' => $blocked->id,
        ]);

        $this->assertDatabaseHas('blocked_users', [
            'blocker_id' => $blocker->id,
            'blocked_id' => $blocked->id,
        ]);

        $this->assertNotNull($blockedUser->created_at);
        $this->assertNotNull($blockedUser->updated_at);
    }

    public function test_has_timestamps(): void
    {
        $blocker = User::factory()->create();
        $blocked = User::factory()->create();

        $blockedUser = BlockedUser::create([
            'blocker_id' => $blocker->id,
            'blocked_id' => $blocked->id,
        ]);

        $this->assertNotNull($blockedUser->created_at);
        $this->assertNotNull($blockedUser->updated_at);
        $this->assertInstanceOf(\Carbon\CarbonInterface::class, $blockedUser->created_at);
        $this->assertInstanceOf(\Carbon\CarbonInterface::class, $blockedUser->updated_at);
    }

    public function test_multiple_blocks_can_exist(): void
    {
        $blocker = User::factory()->create();
        $blocked1 = User::factory()->create();
        $blocked2 = User::factory()->create();

        BlockedUser::create([
            'blocker_id' => $blocker->id,
            'blocked_id' => $blocked1->id,
        ]);

        BlockedUser::create([
            'blocker_id' => $blocker->id,
            'blocked_id' => $blocked2->id,
        ]);

        $this->assertCount(2, BlockedUser::where('blocker_id', $blocker->id)->get());
    }
}
