<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Assert;

use function Pest\Laravel\get;

beforeEach(fn () => User::factory()->count(25)->create());

it('should renders welcome page with users paginated by 15', function () {

    $response = get(route('home'));

    $response
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('welcome')
            ->has('paginator', fn (AssertableInertia $paginator) => $paginator
                ->where('per_page', 15)
                ->where('total', 25)
                ->has('data', 15)
                ->etc()
            )

        );

    /** @var Inertia\Response $inertiaResponse */
    $inertiaResponse = $response->getOriginalContent();

    $props = $inertiaResponse->getData()['page']['props'];

    $paginator = $props['paginator'];

    Assert::assertSame(25, $paginator['total']);
    Assert::assertSame(15, $paginator['per_page']);

    Assert::assertCount(15, $paginator['data']);

    $returnedIds = collect($paginator['data'])->pluck('id')->all();

    Assert::assertEmpty(
        array_diff($returnedIds, User::pluck('id')->all()),
        'Há IDs inválidos no paginator'
    );

    $first15Factory = User::pluck('id')->take(15)->all();
    $diffAssoc = array_diff_assoc($first15Factory, $returnedIds);
    Assert::assertNotEmpty(
        $diffAssoc,
        'Pelo menos um usuário deveria estar em posição diferente'
    );
});

it('renders second page with remaining users and no overlap with first page', function () {
    // page 1
    $first = get(route('home'));
    // page 2
    $second = get(route('home').'?page=2');

    $first
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('welcome')
            ->has('paginator', fn (AssertableInertia $paginator) => $paginator
                ->where('per_page', 15)
                ->where('total', 25)
                ->has('data', 15)
                ->etc()
            )
        );

    $second
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('welcome')
            ->has('paginator', fn (AssertableInertia $paginator) => $paginator
                ->where('per_page', 15)
                ->where('total', 25)
                ->has('data', 10) // remaining 10 users
                ->etc()
            )
        );

    /** @var Inertia\Response $inertiaResponse1 */
    $inertiaResponse1 = $first->getOriginalContent();
    $props1 = $inertiaResponse1->getData()['page']['props'];
    $data1 = $props1['paginator']['data'];
    $ids1 = collect($data1)->pluck('id')->all();

    /** @var Inertia\Response $inertiaResponse2 */
    $inertiaResponse2 = $second->getOriginalContent();
    $props2 = $inertiaResponse2->getData()['page']['props'];
    $data2 = $props2['paginator']['data'];
    $ids2 = collect($data2)->pluck('id')->all();

    // Pages should be disjoint
    Assert::assertEmpty(array_intersect($ids1, $ids2), 'Page 1 and Page 2 should not share user IDs.');

    // Combined, they should cover all users
    $union = collect($ids1)->merge($ids2)->unique()->sort()->values()->all();
    $allIds = User::pluck('id')->sort()->values()->all();
    Assert::assertSame($allIds, $union, 'Union of page 1 and page 2 IDs should equal all user IDs.');
});

it('treats invalid page values as page 1', function () {
    $page1 = get(route('home'));
    $pageZero = get(route('home').'?page=0');
    $pageNegative = get(route('home').'?page=-1');
    $pageNonNumeric = get(route('home').'?page=foo');

    $extractIds = function ($response) {
        /** @var Inertia\Response $inertia */
        $inertia = $response->getOriginalContent();
        $props = $inertia->getData()['page']['props'];

        return collect($props['paginator']['data'])->pluck('id')->all();
    };

    $ids1 = $extractIds($page1);

    Assert::assertSame($ids1, $extractIds($pageZero), 'page=0 should behave like page 1.');
    Assert::assertSame($ids1, $extractIds($pageNegative), 'page=-1 should behave like page 1.');
    Assert::assertSame($ids1, $extractIds($pageNonNumeric), 'page=foo should behave like page 1.');
});

it('returns an empty dataset when requesting a page beyond the last', function () {
    // With 25 total and 15 per page, last page is 2.
    $response = get(route('home').'?page=999');

    $response
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('welcome')
            ->has('paginator', fn (AssertableInertia $paginator) => $paginator
                ->where('per_page', 15)
                ->where('total', 25)
                ->has('data', 0)
                ->etc()
            )
        );

    /** @var Inertia\Response $inertiaResponse */
    $inertiaResponse = $response->getOriginalContent();
    $props = $inertiaResponse->getData()['page']['props'];
    Assert::assertSame(0, count($props['paginator']['data']));
});

it('renders gracefully when there are no users', function () {
    // Override the default beforeEach seeding
    User::query()->delete();

    $response = get(route('home'));

    $response
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('welcome')
            ->has('paginator', fn (AssertableInertia $paginator) => $paginator
                ->where('per_page', 15)
                ->where('total', 0)
                ->has('data', 0)
                ->etc()
            )
        );

    /** @var Inertia\Response $inertiaResponse */
    $inertiaResponse = $response->getOriginalContent();
    $props = $inertiaResponse->getData()['page']['props'];
    Assert::assertSame(0, $props['paginator']['total']);
    Assert::assertCount(0, $props['paginator']['data']);
});

it('renders a single full page when total equals per_page (15)', function () {
    // Start fresh with exactly 15 users
    User::query()->delete();
    User::factory()->count(15)->create();

    $response = get(route('home'));

    $response
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('welcome')
            ->has('paginator', fn (AssertableInertia $paginator) => $paginator
                ->where('per_page', 15)
                ->where('total', 15)
                ->has('data', 15)
                ->etc()
            )
        );

    /** @var Inertia\Response $inertiaResponse */
    $inertiaResponse = $response->getOriginalContent();
    $props = $inertiaResponse->getData()['page']['props'];
    $returnedIds = collect($props['paginator']['data'])->pluck('id')->all();
    $expectedIds = User::pluck('id')->all();

    sort($returnedIds);
    sort($expectedIds);
    Assert::assertSame($expectedIds, $returnedIds, 'Returned IDs should match all users when total equals per_page.');
});
