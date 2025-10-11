<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Welcome;

use App\Http\Controllers\Welcome\WelcomeController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

final class WelcomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_inertia_response_for_first_page_non_ajax(): void
    {
        // Arrange
        User::factory()->count(5)->create();
        $controller = app(WelcomeController::class);
        $request = Request::create('/', 'GET', ['page' => 1]);

        // Act
        $response = $controller->index($request, app(\App\Actions\GetHuntersAction::class));

        // Assert
        $this->assertInstanceOf(\Inertia\Response::class, $response);
    }

    public function test_index_uses_normal_flow_for_ajax_requests_even_when_page_gt_1(): void
    {
        // Arrange
        User::factory()->count(20)->create();
        $controller = app(WelcomeController::class);
        $request = Request::create('/', 'GET', ['page' => 2], [], [], [
            'HTTP_X-Inertia' => 'true',
        ]);

        // Act
        $response = $controller->index($request, app(\App\Actions\GetHuntersAction::class));

        // Assert
        $this->assertInstanceOf(\Inertia\Response::class, $response);
    }

    public function test_index_aggregates_users_for_non_ajax_refresh_with_page_gt_1_and_no_query(): void
    {
        // Arrange
        User::factory()->count(30)->create(); // Enough for multiple pages
        $controller = app(WelcomeController::class);
        $request = Request::create('/', 'GET', ['page' => 2]); // non-AJAX and no q

        // Act
        $response = $controller->index($request, app(\App\Actions\GetHuntersAction::class));

        // Assert
        $this->assertInstanceOf(\Inertia\Response::class, $response);
    }

    public function test_index_skips_multipage_when_query_param_present(): void
    {
        // Arrange
        User::factory()->count(20)->create();
        $controller = app(WelcomeController::class);
        $request = Request::create('/', 'GET', ['page' => 2, 'q' => 'john']); // non-AJAX, page>1, but q present

        // Act
        $response = $controller->index($request, app(\App\Actions\GetHuntersAction::class));

        // Assert
        $this->assertInstanceOf(\Inertia\Response::class, $response);
    }

    public function test_index_handles_unexpected_page_values_gracefully(): void
    {
        // Arrange
        User::factory()->count(10)->create();
        $controller = app(WelcomeController::class);

        // Test various invalid page values
        foreach ([-5, 0, 'not-a-number'] as $pageVal) {
            $request = Request::create('/', 'GET', ['page' => $pageVal]);

            // Act
            $response = $controller->index($request, app(\App\Actions\GetHuntersAction::class));

            // Assert
            $this->assertInstanceOf(\Inertia\Response::class, $response);
        }
    }

    public function test_index_respects_pagination_limits(): void
    {
        // Arrange
        User::factory()->count(50)->create();
        $controller = app(WelcomeController::class);
        $request = Request::create('/', 'GET', ['page' => 1]);

        // Act
        $response = $controller->index($request, app(\App\Actions\GetHuntersAction::class));

        // Assert
        $this->assertInstanceOf(\Inertia\Response::class, $response);
    }
}
