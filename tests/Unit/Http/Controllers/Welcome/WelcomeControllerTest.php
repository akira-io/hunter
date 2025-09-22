<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Welcome;

use App\Actions\GetHuntersAction;
use App\Http\Controllers\Welcome\WelcomeController;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Facade;
use Inertia\Inertia;
use Inertia\Response;
use Mockery;
use Tests\TestCase;

final class WelcomeControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_returns_inertia_response_for_first_page_non_ajax(): void
    {
        // Arrange
        $controller = new WelcomeController();

        $request = Request::create('/', 'GET', ['page' => 1]);
        $action = Mockery::mock(GetHuntersAction::class);

        $users = [['id' => 1], ['id' => 2]];
        $paginator = new LengthAwarePaginator([], 0, 15);

        $action->shouldReceive('handle')
            ->once()
            ->with(Mockery::on(function ($arg) use ($request) {
                // Original request is passed through in normal flow
                return $arg instanceof Request
                    && (int)($arg->get('page', 1)) === (int)$request->get('page', 1);
            }))
            ->andReturn([$users, $paginator]);

        Inertia::shouldReceive('render')
            ->once()
            ->with('welcome', Mockery::on(function ($props) use ($paginator) {
                // We can't assert exact Inertia::merge payload; assert presence and paginator identity.
                return is_array($props)
                    && array_key_exists('users', $props)
                    && array_key_exists('paginator', $props)
                    && $props['paginator'] === $paginator;
            }))
            ->andReturn(new Response('welcome', []));

        // Act
        $response = $controller->index($request, $action);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
    }

    public function test_index_uses_normal_flow_for_ajax_requests_even_when_page_gt_1(): void
    {
        // Arrange
        $controller = new WelcomeController();

        $request = Request::create('/', 'GET', ['page' => 3], [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $action = Mockery::mock(GetHuntersAction::class);

        $users = [['id' => 10], ['id' => 11]];
        $paginator = new LengthAwarePaginator([], 0, 15);

        $action->shouldReceive('handle')
            ->once()
            ->with(Mockery::type(Request::class))
            ->andReturn([$users, $paginator]);

        Inertia::shouldReceive('render')
            ->once()
            ->with('welcome', Mockery::on(function ($props) use ($paginator) {
                return is_array($props)
                    && array_key_exists('users', $props)
                    && $props['paginator'] === $paginator;
            }))
            ->andReturn(new Response('welcome', []));

        // Act
        $response = $controller->index($request, $action);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
    }

    public function test_index_aggregates_users_for_non_ajax_refresh_with_page_gt_1_and_no_query(): void
    {
        // Arrange
        $controller = new WelcomeController();

        $request = Request::create('/', 'GET', ['page' => 3]); // non-AJAX and no q

        $action = Mockery::mock(GetHuntersAction::class);

        $usersPage1 = [['id' => 1]];
        $usersPage2 = [['id' => 2], ['id' => 3]];
        $usersPage3 = [['id' => 4]];

        $p1 = (object)['page' => 1];
        $p2 = (object)['page' => 2];
        $p3 = (object)['page' => 3];

        // Expect three ordered calls with cloned requests for page 1..3
        $action->shouldReceive('handle')
            ->once()
            ->with(Mockery::on(fn(Request $r) => (int)$r->get('page') === 1))
            ->ordered('multi')
            ->andReturn([$usersPage1, $p1]);

        $action->shouldReceive('handle')
            ->once()
            ->with(Mockery::on(fn(Request $r) => (int)$r->get('page') === 2))
            ->ordered('multi')
            ->andReturn([$usersPage2, $p2]);

        $action->shouldReceive('handle')
            ->once()
            ->with(Mockery::on(fn(Request $r) => (int)$r->get('page') === 3))
            ->ordered('multi')
            ->andReturn([$usersPage3, $p3]);

        Inertia::shouldReceive('render')
            ->once()
            ->with('welcome', Mockery::on(function ($props) use ($p3) {
                if (\!is_array($props) || \!isset($props['users'], $props['paginator'])) {
                    return false;
                }
                // Assert concatenation order and final paginator
                $ids = array_map(fn($u) => $u['id'], $props['users']);
                return $ids === [1, 2, 3, 4] && $props['paginator'] === $p3;
            }))
            ->andReturn(new Response('welcome', []));

        // Act
        $response = $controller->index($request, $action);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
    }

    public function test_index_skips_multipage_when_query_param_present(): void
    {
        // Arrange
        $controller = new WelcomeController();
        $request = Request::create('/', 'GET', ['page' => 4, 'q' => 'john']); // non-AJAX, page>1, but q present

        $action = Mockery::mock(GetHuntersAction::class);

        $users = [['id' => 99]];
        $paginator = new LengthAwarePaginator([], 0, 15);

        $action->shouldReceive('handle')
            ->once()
            ->with(Mockery::type(Request::class))
            ->andReturn([$users, $paginator]);

        Inertia::shouldReceive('render')
            ->once()
            ->with('welcome', Mockery::on(function ($props) use ($paginator) {
                return is_array($props)
                    && array_key_exists('users', $props)
                    && $props['paginator'] === $paginator;
            }))
            ->andReturn(new Response('welcome', []));

        // Act
        $response = $controller->index($request, $action);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
    }

    public function test_index_handles_unexpected_page_values_gracefully(): void
    {
        // Arrange
        $controller = new WelcomeController();

        // A negative/zero page should fall back to normal behavior (not multi-page)
        foreach ([-5, 0, '0', 'not-a-number'] as $pageVal) {
            $request = Request::create('/', 'GET', ['page' => $pageVal]);

            $action = Mockery::mock(GetHuntersAction::class);
            $users = [];
            $paginator = new LengthAwarePaginator([], 0, 15);

            $action->shouldReceive('handle')
                ->once()
                ->with(Mockery::type(Request::class))
                ->andReturn([$users, $paginator]);

            Inertia::shouldReceive('render')
                ->once()
                ->with('welcome', Mockery::on(function ($props) use ($paginator) {
                    return is_array($props)
                        && array_key_exists('users', $props)
                        && $props['paginator'] === $paginator;
                }))
                ->andReturn(new Response('welcome', []));

            // Act
            $response = $controller->index($request, $action);

            // Assert
            $this->assertInstanceOf(Response::class, $response);

            // Reset expectations for next iteration
            Mockery::close();
        }
    }
}