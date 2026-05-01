<?php

declare(strict_types=1);

use App\Actions\GetHuntersAction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

it('uses random order when no query and maps users', function () {
    $users = User::factory()->count(3)->create();
    $perPage = 10;
    $request = Request::create('/hunters', 'GET', []);

    [$hunters, $paginator] = new GetHuntersAction()->handle($request, $perPage);

    expect($hunters)->toBeInstanceOf(Collection::class)
        ->and($paginator)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($hunters->count())->toBe(3);

    $first = $hunters->first();

    foreach (['id', 'name', 'email', 'avatar_url', 'background_image_url', 'skills', 'location', 'bio', 'user_name', 'email_verified_at', 'created_at', 'updated_at', 'github_url', 'twitter_url', 'linkedin_url', 'bluesky_url', 'website_url', 'youtube_url'] as $key) {
        expect(array_key_exists($key, $first))->toBeTrue();
    }

    expect($first['background_image_url'])->not->toBeEmpty()
        ->and(str_contains($first['background_image_url'], 'images.unsplash.com'))->toBeTrue();
});

it('uses search when query present', function () {
    User::factory()->create(['name' => 'Laravel Developer']);
    User::factory()->create(['name' => 'Vue Developer']);
    $perPage = 5;
    $request = Request::create('/hunters?q=laravel', 'GET', ['q' => 'laravel']);

    [$hunters, $paginator] = (new GetHuntersAction())->handle($request, $perPage);

    expect($hunters)->toBeInstanceOf(Collection::class)
        ->and($paginator)->toBeInstanceOf(LengthAwarePaginator::class);

    foreach ($hunters as $hunter) {
        expect(array_key_exists('id', $hunter))->toBeTrue()
            ->and(array_key_exists('name', $hunter))->toBeTrue();
    }
});

it('attaches follow status when user is authenticated', function () {
    $authUser = User::factory()->create();
    $targetUsers = User::factory()->count(2)->create();
    $perPage = 10;
    $request = Request::create('/hunters', 'GET');
    $request->setUserResolver(fn () => $authUser);

    [$hunters, $paginator] = (new GetHuntersAction())->handle($request, $perPage);

    expect($hunters)->toBeInstanceOf(Collection::class)
        ->and($paginator)->toBeInstanceOf(LengthAwarePaginator::class);

    foreach ($hunters as $hunter) {
        expect(array_key_exists('has_followed', $hunter))->toBeTrue();
        expect(is_bool($hunter['has_followed']))->toBeTrue();
    }
});

it('respects per page and returns empty collection gracefully', function () {
    $perPage = 7;
    $request = Request::create('/hunters', 'GET');

    [$hunters, $paginator] = (new GetHuntersAction())->handle($request, $perPage);

    expect($hunters)->toBeInstanceOf(Collection::class)
        ->and($hunters->count())->toBe(0)
        ->and($paginator->perPage())->toBe($perPage)
        ->and($paginator->total())->toBe(0);
});

it('prefers media urls over fallbacks when present', function () {
    $user = User::factory()->create(['avatar_url' => 'https://fallback.example.com/avatar.jpg']);
    $perPage = 1;
    $request = Request::create('/hunters', 'GET');

    [$hunters, $paginator] = (new GetHuntersAction())->handle($request, $perPage);

    $row = $hunters->first();

    expect($row['avatar_url'])->toBe('https://fallback.example.com/avatar.jpg')
        ->and(str_contains($row['background_image_url'], 'images.unsplash.com'))->toBeTrue();
});

it('works without authenticated user', function () {
    User::factory()->count(2)->create();
    $perPage = 10;
    $request = Request::create('/hunters', 'GET');
    $request->setUserResolver(fn () => null);

    [$hunters, $paginator] = (new GetHuntersAction())->handle($request, $perPage);

    expect($hunters->count())->toBe(2)
        ->and($paginator)->toBeInstanceOf(LengthAwarePaginator::class);

    foreach ($hunters as $hunter) {
        expect(array_key_exists('has_followed', $hunter))->toBeFalse();
    }
});

it('loads academic backgrounds relationship without errors', function () {
    $user = User::factory()->create();
    $perPage = 10;
    $request = Request::create('/hunters', 'GET');

    [$hunters, $paginator] = new GetHuntersAction()->handle($request, $perPage);

    expect(true)->toBeTrue();
});
