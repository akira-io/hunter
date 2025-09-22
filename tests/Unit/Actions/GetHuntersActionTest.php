<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\GetHuntersAction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mockery as m;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(GetHuntersAction::class)]
final class GetHuntersActionTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function test_handle_uses_random_order_when_no_query_and_maps_users(): void
    {
        // Arrange
        $perPage = 10;
        $request = Request::create('/hunters', 'GET', []); // no q
        $request->setUserResolver(fn () => null); // no auth user

        // Mock the query builder chain: User::query()->inRandomOrder()->paginate($perPage)->withQueryString()
        $queryBuilder = m::mock(\stdClass::class);
        $paginator = $this->fakePaginatorWithUsers(2); // 2 users in collection

        // Expect Eloquent static calls
        m::mock('alias:'.User::class)
            ->shouldReceive('query')->once()->andReturn($queryBuilder);

        m::instance(get_class($queryBuilder), $queryBuilder);

        $queryBuilder->shouldReceive('inRandomOrder')->once()->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('paginate')->once()->with($perPage)->andReturn($paginator);
        $paginator->shouldReceive('withQueryString')->once()->andReturnSelf();

        // Media fallbacks for each user
        foreach ($paginator->getCollection() as $u) {
            $mediaColl = $this->mockMediaCollection(null); // no avatar/background, triggers fallback in action
            $u->shouldReceive('getMedia')->with('profile_avatar')->andReturn($mediaColl);
            $u->shouldReceive('getMedia')->with('profile_background')->andReturn($mediaColl);
        }

        // Act
        [$hunters, $resultPaginator] = (new GetHuntersAction())->handle($request, $perPage);

        // Assert
        $this->assertInstanceOf(Collection::class, $hunters);
        $this->assertCount(2, $hunters);
        $this->assertInstanceOf(LengthAwarePaginatorContract::class, $resultPaginator);

        // Check mapping keys and fallback background URL presence
        $first = $hunters->first();

        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('name', $first);
        $this->assertArrayHasKey('email', $first);
        $this->assertArrayHasKey('avatar_url', $first);
        $this->assertArrayHasKey('background_image_url', $first);
        $this->assertArrayHasKey('skills', $first);
        $this->assertIsArray($first);
        $this->assertNotEmpty($first['background_image_url'], 'Expected fallback background URL when no media exists');
    }

    public function test_handle_uses_search_when_query_present(): void
    {
        // Arrange
        $perPage = 5;
        $request = Request::create('/hunters?q=laravel', 'GET', ['q' => 'laravel']);
        $request->setUserResolver(fn () => null);

        $searchBuilder = m::mock(\stdClass::class);
        $paginator = $this->fakePaginatorWithUsers(1);

        // Expect User::search to be used when q is filled
        m::mock('alias:'.User::class)
            ->shouldReceive('search')->once()->with('laravel')->andReturn($searchBuilder);

        $searchBuilder->shouldReceive('paginate')->once()->with($perPage)->andReturn($paginator);
        $paginator->shouldReceive('withQueryString')->once()->andReturnSelf();

        foreach ($paginator->getCollection() as $u) {
            $mediaColl = $this->mockMediaCollection(null);
            $u->shouldReceive('getMedia')->with('profile_avatar')->andReturn($mediaColl);
            $u->shouldReceive('getMedia')->with('profile_background')->andReturn($mediaColl);
        }

        // Act
        [$hunters, $resultPaginator] = (new GetHuntersAction())->handle($request, $perPage);

        // Assert
        $this->assertCount(1, $hunters);
        $this->assertInstanceOf(LengthAwarePaginatorContract::class, $resultPaginator);
    }

    public function test_handle_attaches_follow_status_when_user_is_authenticated(): void
    {
        // Arrange
        $perPage = 3;
        $request = Request::create('/hunters', 'GET');
        $authUser = m::mock(User::class)->makePartial();
        $request->setUserResolver(fn () => $authUser);

        $queryBuilder = m::mock(\stdClass::class);
        $paginator = $this->fakePaginatorWithUsers(3);

        m::mock('alias:'.User::class)
            ->shouldReceive('query')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('inRandomOrder')->once()->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('paginate')->once()->with($perPage)->andReturn($paginator);
        $paginator->shouldReceive('withQueryString')->once()->andReturnSelf();

        foreach ($paginator->getCollection() as $u) {
            $mediaColl = $this->mockMediaCollection(null);
            $u->shouldReceive('getMedia')->with('profile_avatar')->andReturn($mediaColl);
            $u->shouldReceive('getMedia')->with('profile_background')->andReturn($mediaColl);
        }

        // The action calls $authUser->attachFollowStatus($hunters)
        $authUser->shouldReceive('attachFollowStatus')
            ->once()
            ->with(m::on(function ($collection) {
                return $collection instanceof Collection && $collection->count() === 3;
            }))
            ->andReturnUsing(fn ($c) => $c->map(function (array $row) {
                $row['is_followed'] = false;
                return $row;
            }));

        // Act
        [$hunters, $resultPaginator] = (new GetHuntersAction())->handle($request, $perPage);

        // Assert
        $this->assertTrue($hunters->every(fn ($row) => array_key_exists('is_followed', $row)));
        $this->assertInstanceOf(LengthAwarePaginatorContract::class, $resultPaginator);
    }

    public function test_handle_respects_per_page_and_returns_empty_collection_gracefully(): void
    {
        // Arrange
        $perPage = 7;
        $request = Request::create('/hunters', 'GET');
        $request->setUserResolver(fn () => null);

        $queryBuilder = m::mock(\stdClass::class);
        $emptyPaginator = $this->fakePaginatorWithUsers(0, perPage: $perPage);

        m::mock('alias:'.User::class)
            ->shouldReceive('query')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('inRandomOrder')->once()->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('paginate')->once()->with($perPage)->andReturn($emptyPaginator);
        $emptyPaginator->shouldReceive('withQueryString')->once()->andReturnSelf();

        // Act
        [$hunters, $resultPaginator] = (new GetHuntersAction())->handle($request, $perPage);

        // Assert
        $this->assertInstanceOf(Collection::class, $hunters);
        $this->assertCount(0, $hunters);
        $this->assertSame($perPage, $resultPaginator->perPage());
        $this->assertSame(0, $resultPaginator->total());
    }

    public function test_handle_prefers_media_urls_over_fallbacks_when_present(): void
    {
        // Arrange
        $perPage = 1;
        $request = Request::create('/hunters', 'GET');
        $request->setUserResolver(fn () => null);

        $queryBuilder = m::mock(\stdClass::class);
        $paginator = $this->fakePaginatorWithUsers(1);

        m::mock('alias:'.User::class)
            ->shouldReceive('query')->once()->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('inRandomOrder')->once()->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('paginate')->once()->with($perPage)->andReturn($paginator);
        $paginator->shouldReceive('withQueryString')->once()->andReturnSelf();

        $avatarMedia = $this->mockMediaCollection('https://cdn.example.test/avatar.jpg');
        $bgMedia = $this->mockMediaCollection('https://cdn.example.test/bg.jpg');

        $user = $paginator->getCollection()->first();
        $user->shouldReceive('getMedia')->with('profile_avatar')->andReturn($avatarMedia);
        $user->shouldReceive('getMedia')->with('profile_background')->andReturn($bgMedia);

        // Act
        [$hunters] = (new GetHuntersAction())->handle($request, $perPage);

        // Assert
        $row = $hunters->first();
        $this->assertSame('https://cdn.example.test/avatar.jpg', $row['avatar_url']);
        $this->assertSame('https://cdn.example.test/bg.jpg', $row['background_image_url']);
    }

    /**
     * Build a paginator with mocked User models.
     *
     * @param int $count
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    private function fakePaginatorWithUsers(int $count, int $perPage = 10): LengthAwarePaginator
    {
        $items = [];
        for ($i = 1; $i <= $count; $i++) {
            $user = m::mock(User::class)->makePartial();
            // Basic attributes used by mapping
            $user->id = $i;
            $user->name = "User {$i}";
            $user->email = "user{$i}@example.test";
            $user->avatar_url = "https://img.example.test/u{$i}.png";
            $user->location = 'Earth';
            $user->bio = "Bio {$i}";
            $user->user_name = "user{$i}";
            $user->email_verified_at = now();
            $user->created_at = now()->subDays($i);
            $user->updated_at = now()->subDays($i - 1);
            $user->skills = ['php', 'laravel'];
            $user->github_url = 'https://github.com/example';
            $user->twitter_url = 'https://x.com/example';
            $user->linkedin_url = 'https://linkedin.com/in/example';
            $user->bluesky_url = 'https://bsky.app/profile/example';
            $user->website_url = 'https://example.test';
            $user->youtube_url = 'https://youtube.com/@example';

            // Eager loads of academicBackgrounds won't break
            $user->shouldReceive('load')->byDefault();

            $items[] = $user;
        }

        $collection = new Collection($items);

        // Use a real paginator object with our collection
        $paginator = m::mock(LengthAwarePaginator::class, [$collection, $collection->count(), $perPage, 1, [
            'path' => '/',
        ]])->makePartial();

        // Ensure getCollection() returns our collection instance by reference
        $paginator->shouldReceive('getCollection')->andReturn($collection);

        return $paginator;
    }

    /**
     * Create a mocked media collection for Spatie Media Library-like API.
     *
     * @param string|null $url
     * @return \Illuminate\Support\Collection
     */
    private function mockMediaCollection(?string $url): Collection
    {
        if ($url === null) {
            // Simulate no media: last() returns null
            return new Collection();
        }

        $media = new class($url)
        {
            public function __construct(private string $url) {}
            public function getUrl(): string { return $this->url; }
        };

        // last() should return an object with getUrl()
        return new Collection([$media]);
    }
}