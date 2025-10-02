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
            ->has('users', fn (AssertableInertia $users) => $users
                ->where('per_page', 15)
                ->where('total', 25)
                ->has('data', 15)
                ->etc()
            )

        );

    /** @var Inertia\Response $inertiaResponse */
    $inertiaResponse = $response->getOriginalContent();

    $props = $inertiaResponse->getData()['page']['props'];

    $paginator = $props['users'];

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

it('renders second page with remaining users and proper pagination structure', function () {
    // page 1
    $first = get(route('home'));
    // page 2
    $second = get(route('home').'?page=2');

    $first
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('welcome')
            ->has('users', fn (AssertableInertia $users) => $users
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
            ->has('users', fn (AssertableInertia $users) => $users
                ->where('per_page', 15)
                ->where('total', 25)
                ->has('data', 10) // remaining 10 users
                ->etc()
            )
        );

    /** @var Inertia\Response $inertiaResponse1 */
    $inertiaResponse1 = $first->getOriginalContent();
    $props1 = $inertiaResponse1->getData()['page']['props'];
    $data1 = $props1['users']['data'];
    $ids1 = collect($data1)->pluck('id')->all();

    /** @var Inertia\Response $inertiaResponse2 */
    $inertiaResponse2 = $second->getOriginalContent();
    $props2 = $inertiaResponse2->getData()['page']['props'];
    $data2 = $props2['users']['data'];
    $ids2 = collect($data2)->pluck('id')->all();

    // Verify we got valid user IDs
    $allUserIds = User::pluck('id')->all();
    Assert::assertEmpty(array_diff($ids1, $allUserIds), 'Page 1 should only contain valid user IDs');
    Assert::assertEmpty(array_diff($ids2, $allUserIds), 'Page 2 should only contain valid user IDs');

    // Verify pagination totals are correct
    Assert::assertCount(15, $ids1, 'Page 1 should have 15 users');
    Assert::assertCount(10, $ids2, 'Page 2 should have 10 users');

    // Note: Due to inRandomOrder(), we cannot guarantee no overlap between pages
    // but we can verify the structure and counts are correct
});

it('treats invalid page values correctly and returns proper structure', function () {
    $page1 = get(route('home'));
    $pageZero = get(route('home').'?page=0');
    $pageNegative = get(route('home').'?page=-1');
    $pageNonNumeric = get(route('home').'?page=foo');

    $extractPaginatorData = function ($response) {
        /** @var Inertia\Response $inertia */
        $inertia = $response->getOriginalContent();
        $props = $inertia->getData()['page']['props'];

        return $props['users'];
    };

    $paginator1 = $extractPaginatorData($page1);
    $paginatorZero = $extractPaginatorData($pageZero);
    $paginatorNegative = $extractPaginatorData($pageNegative);
    $paginatorNonNumeric = $extractPaginatorData($pageNonNumeric);

    // Verify all invalid pages return the same structure as page 1
    Assert::assertSame($paginator1['total'], $paginatorZero['total'], 'page=0 should have same total as page 1.');
    Assert::assertSame($paginator1['per_page'], $paginatorZero['per_page'], 'page=0 should have same per_page as page 1.');
    Assert::assertCount(count($paginator1['data']), $paginatorZero['data'], 'page=0 should have same count as page 1.');

    Assert::assertSame($paginator1['total'], $paginatorNegative['total'], 'page=-1 should have same total as page 1.');
    Assert::assertSame($paginator1['per_page'], $paginatorNegative['per_page'], 'page=-1 should have same per_page as page 1.');
    Assert::assertCount(count($paginator1['data']), $paginatorNegative['data'], 'page=-1 should have same count as page 1.');

    Assert::assertSame($paginator1['total'], $paginatorNonNumeric['total'], 'page=foo should have same total as page 1.');
    Assert::assertSame($paginator1['per_page'], $paginatorNonNumeric['per_page'], 'page=foo should have same per_page as page 1.');
    Assert::assertCount(count($paginator1['data']), $paginatorNonNumeric['data'], 'page=foo should have same count as page 1.');

    // Note: Due to inRandomOrder(), we cannot guarantee exact same results,
    // but we can verify the structure is consistent
});

it('returns an empty dataset when requesting a page beyond the last', function () {
    // With 25 total and 15 per page, last page is 2.
    $response = get(route('home').'?page=999');

    $response
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('welcome')
            ->has('users', fn (AssertableInertia $users) => $users
                ->where('per_page', 15)
                ->where('total', 25)
                ->has('data', 0)
                ->etc()
            )
        );

    /** @var Inertia\Response $inertiaResponse */
    $inertiaResponse = $response->getOriginalContent();
    $props = $inertiaResponse->getData()['page']['props'];
    Assert::assertSame(0, count($props['users']['data']));
});

it('renders gracefully when there are no users', function () {
    // Override the default beforeEach seeding
    User::query()->delete();

    $response = get(route('home'));

    $response
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('welcome')
            ->has('users', fn (AssertableInertia $users) => $users
                ->where('per_page', 15)
                ->where('total', 0)
                ->has('data', 0)
                ->etc()
            )
        );

    /** @var Inertia\Response $inertiaResponse */
    $inertiaResponse = $response->getOriginalContent();
    $props = $inertiaResponse->getData()['page']['props'];
    Assert::assertSame(0, $props['users']['total']);
    Assert::assertCount(0, $props['users']['data']);
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
            ->has('users', fn (AssertableInertia $users) => $users
                ->where('per_page', 15)
                ->where('total', 15)
                ->has('data', 15)
                ->etc()
            )
        );

    /** @var Inertia\Response $inertiaResponse */
    $inertiaResponse = $response->getOriginalContent();
    $props = $inertiaResponse->getData()['page']['props'];
    $returnedIds = collect($props['users']['data'])->pluck('id')->all();
    $expectedIds = User::pluck('id')->all();

    sort($returnedIds);
    sort($expectedIds);
    Assert::assertSame($expectedIds, $returnedIds, 'Returned IDs should match all users when total equals per_page.');
});
