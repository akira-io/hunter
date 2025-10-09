<?php

declare(strict_types=1);

namespace Tests\Unit\DataTransferObjects\Metrics;

use App\DataTransferObjects\Metrics\MetricsContext;
use App\Models\Hunt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetricsContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_metrics_context_with_all_properties(): void
    {
        $user = User::factory()->create();
        $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

        $context = new MetricsContext(
            model: $hunt,
            views: 1000,
            likes: 100,
            comments: 50,
            shares: 25,
            calculated: ['engagement_rate' => 17.5]
        );

        $this->assertSame($hunt, $context->model);
        $this->assertEquals(1000, $context->views);
        $this->assertEquals(100, $context->likes);
        $this->assertEquals(50, $context->comments);
        $this->assertEquals(25, $context->shares);
        $this->assertEquals(['engagement_rate' => 17.5], $context->calculated);
    }

    public function test_creates_context_with_empty_calculated_array_by_default(): void
    {
        $user = User::factory()->create();
        $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

        $context = new MetricsContext(
            model: $hunt,
            views: 100,
            likes: 10,
            comments: 5,
            shares: 2
        );

        $this->assertEquals([], $context->calculated);
    }

    public function test_with_calculated_creates_new_immutable_instance(): void
    {
        $user = User::factory()->create();
        $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

        $originalContext = new MetricsContext(
            model: $hunt,
            views: 1000,
            likes: 100,
            comments: 50,
            shares: 25,
            calculated: ['engagement_rate' => 17.5]
        );

        $newContext = $originalContext->withCalculated([
            'interaction_rate' => 15.0,
            'quality_score' => 75.0,
        ]);

        // Original should remain unchanged
        $this->assertEquals(['engagement_rate' => 17.5], $originalContext->calculated);

        // New context should have merged metrics
        $this->assertEquals([
            'engagement_rate' => 17.5,
            'interaction_rate' => 15.0,
            'quality_score' => 75.0,
        ], $newContext->calculated);

        // All other properties should be copied
        $this->assertSame($hunt, $newContext->model);
        $this->assertEquals(1000, $newContext->views);
        $this->assertEquals(100, $newContext->likes);
    }

    public function test_with_calculated_merges_with_existing_metrics(): void
    {
        $user = User::factory()->create();
        $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

        $context = new MetricsContext(
            model: $hunt,
            views: 1000,
            likes: 100,
            comments: 50,
            shares: 25,
            calculated: [
                'metric1' => 10,
                'metric2' => 20,
            ]
        );

        $updated = $context->withCalculated([
            'metric3' => 30,
            'metric4' => 40,
        ]);

        $this->assertEquals([
            'metric1' => 10,
            'metric2' => 20,
            'metric3' => 30,
            'metric4' => 40,
        ], $updated->calculated);
    }

    public function test_with_calculated_overwrites_existing_keys(): void
    {
        $user = User::factory()->create();
        $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

        $context = new MetricsContext(
            model: $hunt,
            views: 1000,
            likes: 100,
            comments: 50,
            shares: 25,
            calculated: [
                'engagement_rate' => 17.5,
                'quality_score' => 75.0,
            ]
        );

        $updated = $context->withCalculated([
            'engagement_rate' => 20.0, // Overwrite existing
            'new_metric' => 99.9,
        ]);

        $this->assertEquals([
            'engagement_rate' => 20.0, // Updated value
            'quality_score' => 75.0,   // Preserved
            'new_metric' => 99.9,       // New
        ], $updated->calculated);
    }

    public function test_get_returns_calculated_metric_value(): void
    {
        $user = User::factory()->create();
        $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

        $context = new MetricsContext(
            model: $hunt,
            views: 1000,
            likes: 100,
            comments: 50,
            shares: 25,
            calculated: [
                'engagement_rate' => 17.5,
                'quality_score' => 75.0,
            ]
        );

        $this->assertEquals(17.5, $context->get('engagement_rate'));
        $this->assertEquals(75.0, $context->get('quality_score'));
    }

    public function test_get_returns_default_when_key_not_found(): void
    {
        $user = User::factory()->create();
        $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

        $context = new MetricsContext(
            model: $hunt,
            views: 1000,
            likes: 100,
            comments: 50,
            shares: 25,
            calculated: []
        );

        $this->assertNull($context->get('non_existent'));
        $this->assertEquals(0, $context->get('non_existent', 0));
        $this->assertEquals('default', $context->get('non_existent', 'default'));
    }

    public function test_has_returns_true_when_key_exists(): void
    {
        $user = User::factory()->create();
        $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

        $context = new MetricsContext(
            model: $hunt,
            views: 1000,
            likes: 100,
            comments: 50,
            shares: 25,
            calculated: [
                'engagement_rate' => 17.5,
                'null_value' => null,
            ]
        );

        $this->assertTrue($context->has('engagement_rate'));
        $this->assertTrue($context->has('null_value')); // array_key_exists checks for key, not value
    }

    public function test_has_returns_false_when_key_does_not_exist(): void
    {
        $user = User::factory()->create();
        $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

        $context = new MetricsContext(
            model: $hunt,
            views: 1000,
            likes: 100,
            comments: 50,
            shares: 25,
            calculated: ['engagement_rate' => 17.5]
        );

        $this->assertFalse($context->has('non_existent_key'));
        $this->assertFalse($context->has('quality_score'));
    }

    public function test_immutability_chain(): void
    {
        $user = User::factory()->create();
        $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

        $context1 = new MetricsContext(
            model: $hunt,
            views: 1000,
            likes: 100,
            comments: 50,
            shares: 25
        );

        $context2 = $context1->withCalculated(['metric1' => 1]);
        $context3 = $context2->withCalculated(['metric2' => 2]);
        $context4 = $context3->withCalculated(['metric3' => 3]);

        // All contexts should be different instances
        $this->assertNotSame($context1, $context2);
        $this->assertNotSame($context2, $context3);
        $this->assertNotSame($context3, $context4);

        // Each should have its own state
        $this->assertEquals([], $context1->calculated);
        $this->assertEquals(['metric1' => 1], $context2->calculated);
        $this->assertEquals(['metric1' => 1, 'metric2' => 2], $context3->calculated);
        $this->assertEquals(['metric1' => 1, 'metric2' => 2, 'metric3' => 3], $context4->calculated);
    }
}
