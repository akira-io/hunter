<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Configure OAuth for both providers
    config(['services.google.client_id' => 'fake-google-client-id']);
    config(['services.google.client_secret' => 'fake-google-client-secret']);
    config(['services.google.redirect' => 'http://localhost/auth/google/callback']);

    config(['services.github.client_id' => 'fake-github-client-id']);
    config(['services.github.client_secret' => 'fake-github-client-secret']);
    config(['services.github.redirect' => 'http://localhost/auth/github/callback']);
});

it('links GitHub to existing Google user when using same email', function () {
    $email = 'user@example.com';

    // Step 1: Create a user who registered with Google
    $existingUser = User::factory()->create([
        'email' => $email,
        'name' => 'John Doe',
        'avatar_url' => 'https://google.com/avatar.jpg',
        'github_id' => null, // No GitHub connection yet
        'bio' => null, // Empty bio to test update
        'location' => null, // Empty location to test update
    ]);

    // Step 2: Same user tries to login with GitHub using same email
    $githubUser = new SocialiteUser();
    $githubUser->id = 'github-456';
    $githubUser->nickname = 'johndoe';
    $githubUser->name = 'John Doe (GitHub)';
    $githubUser->email = $email;
    $githubUser->avatar = 'https://github.com/avatar.jpg';
    $githubUser->token = 'github-token';
    $githubUser->refreshToken = 'github-refresh-token';

    $raw = [
        'login' => 'johndoe',
        'bio' => 'Developer from GitHub',
        'location' => 'GitHub Land',
        'html_url' => 'https://github.com/johndoe',
    ];

    $githubUser->setRaw($raw)->map([
        'id' => 'github-456',
        'nickname' => 'johndoe',
        'name' => 'John Doe (GitHub)',
        'email' => $email,
        'avatar' => 'https://github.com/avatar.jpg',
    ]);

    // Mock GitHub socialite
    Socialite::shouldReceive('driver')->with('github')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($githubUser);

    $response = $this->get('/auth/github/callback');
    $response->assertRedirect(route('hunts.index'));

    // Step 3: Verify that GitHub data was added to existing user
    $existingUser->refresh();
    $this->assertEquals($email, $existingUser->email); // Same email
    $this->assertEquals('John Doe', $existingUser->name); // Original name preserved
    $this->assertEquals('github-456', $existingUser->github_id); // GitHub ID added
    $this->assertEquals('johndoe', $existingUser->user_name); // GitHub username added
    $this->assertEquals('https://github.com/johndoe', $existingUser->github_url); // GitHub URL added
    $this->assertEquals('Developer from GitHub', $existingUser->bio); // Bio updated (was empty)
    $this->assertEquals('GitHub Land', $existingUser->location); // Location updated (was empty)

    // Verify only one user exists
    $this->assertEquals(1, User::where('email', $email)->count());
});

it('does not create duplicate users with same email', function () {
    $email = 'user@example.com';

    // Create user with one provider (simulate Google signup)
    $existingUser = User::factory()->create([
        'email' => $email,
        'name' => 'Original User',
        'github_id' => null,
        'bio' => null, // Empty bio to test update
        'location' => null, // Empty location to test update
    ]);

    // Try to login with GitHub using same email
    $githubUser = new SocialiteUser();
    $githubUser->id = 'github-456';
    $githubUser->nickname = 'githubuser';
    $githubUser->name = 'GitHub User';
    $githubUser->email = $email;
    $githubUser->avatar = 'https://github.com/avatar.jpg';
    $githubUser->token = 'github-token';
    $githubUser->refreshToken = 'github-refresh-token';

    $githubUser->setRaw([
        'login' => 'githubuser',
        'html_url' => 'https://github.com/githubuser',
        'bio' => 'New bio',
        'location' => 'New location',
    ]);

    Socialite::shouldReceive('driver')->with('github')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($githubUser);

    $response = $this->get('/auth/github/callback');
    $response->assertRedirect(route('hunts.index'));

    // Verify only one user exists with this email
    $this->assertEquals(1, User::where('email', $email)->count());

    // Verify original user was updated with GitHub data, not replaced
    $user = User::where('email', $email)->first();
    $this->assertEquals($existingUser->id, $user->id); // Same user ID
    $this->assertEquals('Original User', $user->name); // Original name preserved
    $this->assertEquals('github-456', $user->github_id); // GitHub data added
    $this->assertEquals('githubuser', $user->user_name); // GitHub username added
    $this->assertEquals('New bio', $user->bio); // Bio updated (was empty)
    $this->assertEquals('New location', $user->location); // Location updated (was empty)
});