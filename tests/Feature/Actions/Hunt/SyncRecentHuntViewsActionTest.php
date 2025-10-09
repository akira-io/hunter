<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Hunt;

use App\Actions\Hunt\GetRecentHuntIdsAction;
use App\Actions\Hunt\SyncRecentHuntViewsAction;
use App\Actions\Hunt\UpdateHuntViewsFromPanAction;
use App\Models\Hunt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SyncRecentHuntViewsActionTest extends TestCase
{
    use RefreshDatabase;

    private SyncRecentHuntViewsAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->action = new SyncRecentHuntViewsAction(
            new GetRecentHuntIdsAction(),
            new UpdateHuntViewsFromPanAction()
        );
    }

    public function test_syncs_recent_hunt_views_successfully(): void
    {
        Log::spy();
        
        $user = User::factory()->create();
        
        // Create recent hunts (within last 24 hours)
        $hunt1 = Hunt::factory()->create([
            'owner_id' => $user->id,
            'created_at' => now()->subHours(12),
            'views_count' => 0,
        ]);
        
        $hunt2 = Hunt::factory()->create([
            'owner_id' => $user->id,
            'created_at' => now()->subHours(6),
            'views_count' => 0,
        ]);

        // Add analytics data
        DB::table('pan_analytics')->insert([
            ['name' => "hunt-{$hunt1->id}", 'impressions' => 100, 'hovers' => 10, 'clicks' => 5],
            ['name' => "hunt-{$hunt2->id}", 'impressions' => 50, 'hovers' => 5, 'clicks' => 2],
        ]);

        $result = $this->action->handle();

        $this->assertEquals(2, $result);
        $this->assertEquals(100, $hunt1->fresh()->views_count);
        $this->assertEquals(50, $hunt2->fresh()->views_count);

        Log::shouldHaveReceived('info')
            ->with('Starting RECENT hunt views sync from Pan analytics');

        Log::shouldHaveReceived('info')
            ->with('Recent hunt views sync completed. Synced 2 recent hunts.');
    }

    public function test_returns_zero_when_no_recent_hunts(): void
    {
        Log::spy();

        // Create old hunts (more than 24 hours ago)
        $user = User::factory()->create();
        Hunt::factory()->create([
            'owner_id' => $user->id,
            'created_at' => now()->subDays(2),
        ]);

        $result = $this->action->handle();

        $this->assertEquals(0, $result);

        Log::shouldHaveReceived('info')
            ->with('Starting RECENT hunt views sync from Pan analytics');

        Log::shouldHaveReceived('info')
            ->with('No recent hunts to sync');
    }

    public function test_logs_no_recent_hunts_message(): void
    {
        Log::spy();

        $result = $this->action->handle();

        $this->assertEquals(0, $result);

        Log::shouldHaveReceived('info')
            ->with('No recent hunts to sync');
    }

    public function test_only_syncs_hunts_created_within_last_24_hours(): void
    {
        $user = User::factory()->create();

        // Recent hunt (should be synced)
        $recentHunt = Hunt::factory()->create([
            'owner_id' => $user->id,
            'created_at' => now()->subHours(12),
            'views_count' => 0,
        ]);

        // Old hunt (should NOT be synced)
        $oldHunt = Hunt::factory()->create([
            'owner_id' => $user->id,
            'created_at' => now()->subDays(2),
            'views_count' => 0,
        ]);

        // Add analytics for both
        DB::table('pan_analytics')->insert([
            ['name' => "hunt-{$recentHunt->id}", 'impressions' => 100, 'hovers' => 10, 'clicks' => 5],
            ['name' => "hunt-{$oldHunt->id}", 'impressions' => 200, 'hovers' => 20, 'clicks' => 10],
        ]);

        $result = $this->action->handle();

        // Only 1 hunt should be synced (the recent one)
        $this->assertEquals(1, $result);
        $this->assertEquals(100, $recentHunt->fresh()->views_count);
        $this->assertEquals(0, $oldHunt->fresh()->views_count); // Should remain unchanged
    }

    public function test_handles_empty_collection_gracefully(): void
    {
        Log::spy();

        // No hunts at all
        $result = $this->action->handle();

        $this->assertEquals(0, $result);

        Log::shouldHaveReceived('info')
            ->with('No recent hunts to sync');
    }

    public function test_logs_all_expected_messages_in_sequence(): void
    {
        Log::spy();

        $user = User::factory()->create();
        
        $hunt = Hunt::factory()->create([
            'owner_id' => $user->id,
            'created_at' => now()->subHours(12),
            'views_count' => 0,
        ]);

        DB::table('pan_analytics')->insert([
            'name' => "hunt-{$hunt->id}",
            'impressions' => 100,
            'hovers' => 10,
            'clicks' => 5,
        ]);

        $result = $this->action->handle();

        $this->assertEquals(1, $result);

        // Verify all log messages (covers lines 24, 29-31, 36)
        Log::shouldHaveReceived('info')
            ->times(2); // Starting message + completion message

        Log::shouldHaveReceived('info')
            ->with('Starting RECENT hunt views sync from Pan analytics');

        Log::shouldHaveReceived('info')
            ->with('Recent hunt views sync completed. Synced 1 recent hunts.');
    }
}
