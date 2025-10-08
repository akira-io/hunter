<?php

declare(strict_types=1);

use App\Actions\Hunt\GetRecentHuntIdsAction;
use App\Actions\Hunt\SyncAllHuntViewsAction;
use App\Actions\Hunt\SyncRecentHuntViewsAction;
use App\Actions\Hunt\UpdateHuntViewsFromPanAction;
use App\Jobs\SyncHuntViewsFromPan;
use App\Jobs\SyncRecentHuntViewsFromPan;
use App\Models\Hunt;
use Illuminate\Support\Facades\DB;

test('updates hunt views from pan analytics', function () {
    $hunt1 = Hunt::factory()->create(['views_count' => 0]);
    $hunt2 = Hunt::factory()->create(['views_count' => 0]);

    DB::table('pan_analytics')->insert([
        [
            'name' => "hunt-{$hunt1->id}",
            'impressions' => 150,
            'hovers' => 0,
            'clicks' => 0,
        ],
        [
            'name' => "hunt-{$hunt2->id}",
            'impressions' => 250,
            'hovers' => 0,
            'clicks' => 0,
        ],
    ]);

    $action = new UpdateHuntViewsFromPanAction;
    $syncedCount = $action->handle();

    expect($syncedCount)->toBe(2)
        ->and(DB::table('hunts')->where('id', $hunt1->id)->value('views_count'))->toBe(150)
        ->and(DB::table('hunts')->where('id', $hunt2->id)->value('views_count'))->toBe(250);
});

test('ignores non-hunt analytics entries', function () {
    $hunt = Hunt::factory()->create(['views_count' => 0]);

    DB::table('pan_analytics')->insert([
        [
            'name' => 'other-event',
            'impressions' => 100,
            'hovers' => 0,
            'clicks' => 0,
        ],
        [
            'name' => "hunt-{$hunt->id}",
            'impressions' => 50,
            'hovers' => 0,
            'clicks' => 0,
        ],
    ]);

    $action = new UpdateHuntViewsFromPanAction;
    $syncedCount = $action->handle();

    expect($syncedCount)->toBe(1)
        ->and(DB::table('hunts')->where('id', $hunt->id)->value('views_count'))->toBe(50);
});

test('handles invalid hunt ids gracefully', function () {
    DB::table('pan_analytics')->insert([
        [
            'name' => 'hunt-99999',
            'impressions' => 100,
            'hovers' => 0,
            'clicks' => 0,
        ],
        [
            'name' => 'hunt-invalid-text',
            'impressions' => 50,
            'hovers' => 0,
            'clicks' => 0,
        ],
    ]);

    $action = new UpdateHuntViewsFromPanAction;

    expect(fn () => $action->handle())->not->toThrow(Exception::class);
});

test('gets recent hunt ids correctly', function () {
    $recentHunt = Hunt::factory()->create(['created_at' => now()->subHours(12)]);
    $oldHunt = Hunt::factory()->create(['created_at' => now()->subDays(2)]);

    $action = new GetRecentHuntIdsAction;
    $recentIds = $action->handle();

    expect($recentIds)->toContain($recentHunt->id)
        ->and($recentIds)->not->toContain($oldHunt->id);
});

test('sync all hunts action works', function () {
    $hunt = Hunt::factory()->create(['views_count' => 0]);

    DB::table('pan_analytics')->insert([
        [
            'name' => "hunt-{$hunt->id}",
            'impressions' => 75,
            'hovers' => 0,
            'clicks' => 0,
        ],
    ]);

    $action = app(SyncAllHuntViewsAction::class);
    $count = $action->handle();

    expect($count)->toBeGreaterThan(0)
        ->and(DB::table('hunts')->where('id', $hunt->id)->value('views_count'))->toBe(75);
});

test('sync recent hunts action works', function () {
    $recentHunt = Hunt::factory()->create([
        'views_count' => 0,
        'created_at' => now()->subHours(12),
    ]);

    $oldHunt = Hunt::factory()->create([
        'views_count' => 0,
        'created_at' => now()->subDays(2),
    ]);

    DB::table('pan_analytics')->insert([
        [
            'name' => "hunt-{$recentHunt->id}",
            'impressions' => 100,
            'hovers' => 0,
            'clicks' => 0,
        ],
        [
            'name' => "hunt-{$oldHunt->id}",
            'impressions' => 50,
            'hovers' => 0,
            'clicks' => 0,
        ],
    ]);

    $action = app(SyncRecentHuntViewsAction::class);
    $count = $action->handle();

    expect($count)->toBe(1)
        ->and(DB::table('hunts')->where('id', $recentHunt->id)->value('views_count'))->toBe(100)
        ->and(DB::table('hunts')->where('id', $oldHunt->id)->value('views_count'))->toBe(0);
});

test('job uses sync all action', function () {
    $hunt = Hunt::factory()->create(['views_count' => 0]);

    DB::table('pan_analytics')->insert([
        [
            'name' => "hunt-{$hunt->id}",
            'impressions' => 80,
            'hovers' => 0,
            'clicks' => 0,
        ],
    ]);

    $job = new SyncHuntViewsFromPan;
    $job->handle(app(SyncAllHuntViewsAction::class));

    expect(DB::table('hunts')->where('id', $hunt->id)->value('views_count'))->toBe(80);
});

test('job uses sync recent action', function () {
    $recentHunt = Hunt::factory()->create([
        'views_count' => 0,
        'created_at' => now()->subHours(6),
    ]);

    DB::table('pan_analytics')->insert([
        [
            'name' => "hunt-{$recentHunt->id}",
            'impressions' => 90,
            'hovers' => 0,
            'clicks' => 0,
        ],
    ]);

    $job = new SyncRecentHuntViewsFromPan;
    $job->handle(app(SyncRecentHuntViewsAction::class));

    expect(DB::table('hunts')->where('id', $recentHunt->id)->value('views_count'))->toBe(90);
});
