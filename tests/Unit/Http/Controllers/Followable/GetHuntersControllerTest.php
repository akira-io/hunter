<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Followable;

use App\Http\Controllers\Followable\GetHuntersController;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Inertia\Response as InertiaResponse;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

/**
 * Framework: PHPUnit.
 * If your project uses Pest, you can wrap these in Pest style or convert accordingly.
 */
final class GetHuntersControllerTest extends TestCase
{
    use WithFaker;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_returns_inertia_response_with_followers_prop_and_component_name(): void
    {
        // Arrange: mock user, followers relation, paginator, and attachFollowStatus
        /** @var User&MockInterface $user */
        $user = Mockery::mock(User::class);

        // Build a simple paginator (no DB) for 2 fake followers
        $items = collect([
            ['id' => 10, 'name' => 'Alice'],
            ['id' => 11, 'name' => 'Bob'],
        ]);
        $paginator = new Paginator($items, $items->count(), 20, 1);

        // Mock followers()->paginate(20)
        $relation = new class($paginator)
        {
            private LengthAwarePaginator $p;

            public function __construct(LengthAwarePaginator $p)
            {
                $this->p = $p;
            }

            public function paginate(int $perPage)
            {
                // Use $perPage to satisfy static analysis for unused parameters.

                return $this->p;
            }
        };

        $user->shouldReceive('followers')->once()->andReturn($relation);

        // attachFollowStatus should decorate paginator items; return same paginator for simplicity
        $user->shouldReceive('attachFollowStatus')->once()->with($paginator)->andReturn($paginator);

        $request = $this->makeRequestWithUser($user);

        $controller = new GetHuntersController();

        // Act
        $response = $controller($request);

        // Assert
        $this->assertInstanceOf(InertiaResponse::class, $response, 'Expected an Inertia\Response instance.');
        $this->assertSame('followable/hunters', $response->component(), 'Incorrect Inertia component name.');

        $props = $response->getProps();
        $this->assertArrayHasKey('followers', $props, 'Missing followers prop.');
        $this->assertInstanceOf(LengthAwarePaginator::class, $props['followers'], 'followers prop must be a paginator.');
        $this->assertSame(2, $props['followers']->count(), 'Unexpected followers count.');
    }

    /** @test */
    public function it_paginates_with_page_size_20(): void
    {
        /** @var User&MockInterface $user */
        $user = Mockery::mock(User::class);

        // Track that paginate was called with 20
        $paginator = new Paginator(collect(), 0, 20, 1);

        $relation = Mockery::mock();
        $relation->shouldReceive('paginate')->once()->with(20)->andReturn($paginator);

        $user->shouldReceive('followers')->once()->andReturn($relation);
        $user->shouldReceive('attachFollowStatus')->once()->with($paginator)->andReturn($paginator);

        $request = $this->makeRequestWithUser($user);

        $controller = new GetHuntersController();

        $response = $controller($request);

        $this->assertInstanceOf(InertiaResponse::class, $response);
        $this->assertSame('followable/hunters', $response->component());

        $props = $response->getProps();
        $this->assertArrayHasKey('followers', $props);
        $this->assertInstanceOf(LengthAwarePaginator::class, $props['followers']);
        $this->assertSame(20, $props['followers']->perPage(), 'Expected per-page size of 20.');
    }

    /** @test */
    public function it_handles_empty_followers_gracefully(): void
    {
        /** @var User&MockInterface $user */
        $user = Mockery::mock(User::class);

        $paginator = new Paginator(collect(), 0, 20, 1);

        $relation = Mockery::mock();
        $relation->shouldReceive('paginate')->once()->with(20)->andReturn($paginator);

        $user->shouldReceive('followers')->once()->andReturn($relation);
        $user->shouldReceive('attachFollowStatus')->once()->with($paginator)->andReturn($paginator);

        $request = $this->makeRequestWithUser($user);
        $controller = new GetHuntersController();

        $response = $controller($request);

        $this->assertInstanceOf(InertiaResponse::class, $response);
        $props = $response->getProps();
        $this->assertArrayHasKey('followers', $props);
        $this->assertSame(0, $props['followers']->total(), 'Expected zero followers.');
    }

    /** @test */
    public function it_propagates_errors_from_followers_relation(): void
    {
        /** @var User&MockInterface $user */
        $user = Mockery::mock(User::class);

        $user->shouldReceive('followers')->once()->andThrow(new RuntimeException('Relation failed'));

        $request = $this->makeRequestWithUser($user);
        $controller = new GetHuntersController();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Relation failed');

        $controller($request);
    }

    /** @test */
    public function it_throws_if_request_user_is_not_a_user_instance(): void
    {
        // Some apps implement a strict type() helper; ensure non-User causes a failure.
        // We cannot know exact exception class; assert that an exception is thrown.
        $request = Request::create('/followable/followers', 'GET');
        $request->setUserResolver(fn () => new class() {});

        $controller = new GetHuntersController();

        $caught = false;
        try {
            $controller($request);
        } catch (Throwable $e) {
            $caught = true;
            $this->assertNotEmpty($e->getMessage(), 'Type assertion should produce an error message.');
        }

        $this->assertTrue($caught, 'Expected an exception when request->user() is not App\\Models\\User.');
    }

    /**
     * Helper to build a minimal Request with a mocked authenticated user.
     *
     * @param  User|MockInterface  $user
     */
    private function makeRequestWithUser($user): Request
    {
        $request = Request::create('/followable/followers', 'GET');
        // Bind the user resolver so $request->user() returns our mock.
        $request->setUserResolver(fn () => $user);

        return $request;
    }
}
