<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Assert;

beforeEach(fn () => User::factory()->count(25)->create());

it('should renders welcome page with users paginated by 15', function () {

    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('welcome')
            ->has('users', 15)
            ->has('paginator')
        );

    /** @var Inertia\Response $inertiaResponse */
    $inertiaResponse = $response->getOriginalContent();

    $props = $inertiaResponse->getData()['page']['props'];

    $paginator = $props['paginator'];
    $users = $props['users'];

    Assert::assertSame(25, $paginator['total']);
    Assert::assertSame(15, $paginator['per_page']);

    Assert::assertCount(15, $users);

    $returnedIds = collect($users)->pluck('id')->all();

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

it('renders second page with aggregated users when navigating directly', function () {
    // When navigating directly to page 2 (non-Inertia), the controller aggregates pages 1-2
    // to provide all content up to that page for server-side rendering.
    // Due to inRandomOrder(), some users may appear in both pages, so unique() reduces the total.
    $second = $this->get(route('home').'?page=2');

    $second->assertOk();

    /** @var Inertia\Response $inertiaResponse2 */
    $inertiaResponse2 = $second->getOriginalContent();
    $props2 = $inertiaResponse2->getData()['page']['props'];
    $data2 = $props2['users'];
    $ids2 = collect($data2)->pluck('id')->all();

    // Verify we got valid user IDs
    $allUserIds = User::pluck('id')->all();
    Assert::assertEmpty(array_diff($ids2, $allUserIds), 'Page 2 should only contain valid user IDs');

    // Due to random ordering and unique(), we could get anywhere from 10 to 25 users
    // (10 if all page 2 users are unique, 25 if all users from both pages are unique)
    $userCount = count($ids2);
    Assert::assertGreaterThanOrEqual(10, $userCount, 'Should have at least some users from aggregation');
    Assert::assertLessThanOrEqual(25, $userCount, 'Should have at most all users');
});

it('treats invalid page values correctly and returns proper structure', function () {
    $page1 = $this->get(route('home'));
    $pageZero = $this->get(route('home').'?page=0');
    $pageNegative = $this->get(route('home').'?page=-1');
    $pageNonNumeric = $this->get(route('home').'?page=foo');

    $extractPaginatorData = function ($response) {
        /** @var Inertia\Response $inertia */
        $inertia = $response->getOriginalContent();
        $props = $inertia->getData()['page']['props'];

        return [
            'users' => $props['users'],
            'paginator' => $props['paginator'],
        ];
    };

    $data1 = $extractPaginatorData($page1);
    $dataZero = $extractPaginatorData($pageZero);
    $dataNegative = $extractPaginatorData($pageNegative);
    $dataNonNumeric = $extractPaginatorData($pageNonNumeric);

    // Verify all invalid pages return the same structure as page 1
    Assert::assertSame($data1['paginator']['total'], $dataZero['paginator']['total'], 'page=0 should have same total as page 1.');
    Assert::assertSame($data1['paginator']['per_page'], $dataZero['paginator']['per_page'], 'page=0 should have same per_page as page 1.');
    Assert::assertCount(count($data1['users']), $dataZero['users'], 'page=0 should have same count as page 1.');

    Assert::assertSame($data1['paginator']['total'], $dataNegative['paginator']['total'], 'page=-1 should have same total as page 1.');
    Assert::assertSame($data1['paginator']['per_page'], $dataNegative['paginator']['per_page'], 'page=-1 should have same per_page as page 1.');
    Assert::assertCount(count($data1['users']), $dataNegative['users'], 'page=-1 should have same count as page 1.');

    Assert::assertSame($data1['paginator']['total'], $dataNonNumeric['paginator']['total'], 'page=foo should have same total as page 1.');
    Assert::assertSame($data1['paginator']['per_page'], $dataNonNumeric['paginator']['per_page'], 'page=foo should have same per_page as page 1.');
    Assert::assertCount(count($data1['users']), $dataNonNumeric['users'], 'page=foo should have same count as page 1.');

    // Note: Due to inRandomOrder(), we cannot guarantee exact same results,
    // but we can verify the structure is consistent
});

it('returns an empty dataset when requesting a page beyond the last', function () {
    // With 25 total and 15 per page, last page is 2.
    $response = $this->get(route('home').'?page=999');

    $response
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('welcome')
            ->has('users', 0)
            ->has('paginator')
        );

    /** @var Inertia\Response $inertiaResponse */
    $inertiaResponse = $response->getOriginalContent();
    $props = $inertiaResponse->getData()['page']['props'];
    Assert::assertSame(0, count($props['users']));
});

it('renders gracefully when there are no users', function () {
    // Override the default beforeEach seeding
    User::query()->delete();

    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('welcome')
            ->has('users', 0)
            ->has('paginator')
        );

    /** @var Inertia\Response $inertiaResponse */
    $inertiaResponse = $response->getOriginalContent();
    $props = $inertiaResponse->getData()['page']['props'];
    Assert::assertSame(0, $props['paginator']['total']);
    Assert::assertCount(0, $props['users']);
});

it('renders a single full page when total equals per_page (15)', function () {
    // Start fresh with exactly 15 users
    User::query()->delete();
    User::factory()->count(15)->create();

    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('welcome')
            ->has('users', 15)
            ->has('paginator')
        );

    /** @var Inertia\Response $inertiaResponse */
    $inertiaResponse = $response->getOriginalContent();
    $props = $inertiaResponse->getData()['page']['props'];
    $returnedIds = collect($props['users'])->pluck('id')->all();
    $expectedIds = User::pluck('id')->all();

    sort($returnedIds);
    sort($expectedIds);
    Assert::assertSame($expectedIds, $returnedIds, 'Returned IDs should match all users when total equals per_page.');
});
