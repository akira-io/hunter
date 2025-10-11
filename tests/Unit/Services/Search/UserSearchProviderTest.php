<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Search\Providers\UserSearchProvider;

it('returns public profile route for a user via getRedirectUrl', function () {
    $user = User::factory()->create();

    $provider = app(UserSearchProvider::class);

    $url = $provider->getRedirectUrl($user);

    expect($url)->toBe(route('public.profile.show', ['user' => $user->id]));
});
