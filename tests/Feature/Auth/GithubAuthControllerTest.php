<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Configure GitHub OAuth
    config(['services.github.client_id' => 'fake-client-id']);
    config(['services.github.client_secret' => 'fake-client-secret']);
    config(['services.github.redirect' => 'http://localhost/auth/github/callback']);
});

it('redirects to github', function () {
    $response = $this->get('/auth/github');

    $response->assertRedirectContains('github.com/login/oauth/authorize');
});

it('creates a new user when github user does not exist', function () {
    $this->withoutExceptionHandling();

    // Create test data
    $githubId = '123456';
    $userName = 'githubuser';
    $email = 'user@example.com';

    // Create a mock Socialite user
    $socialiteUser = new SocialiteUser();
    $socialiteUser->id = $githubId;
    $socialiteUser->nickname = $userName;
    $socialiteUser->name = 'GitHub User';
    $socialiteUser->email = $email;
    $socialiteUser->avatar = 'https://github.com/avatar.jpg';
    $socialiteUser->token = 'github-token';
    $socialiteUser->refreshToken = 'github-refresh-token';

    // Set raw data
    $raw = [
        'login' => $userName,
        'bio' => 'Developer',
        'location' => 'Earth',
        'html_url' => 'https://github.com/'.$userName,
    ];

    $socialiteUser->setRaw($raw)->map([
        'id' => $githubId,
        'nickname' => $userName,
        'name' => 'GitHub User',
        'email' => $email,
        'avatar' => 'https://github.com/avatar.jpg',
    ]);

    // Mock the Socialite facade
    Socialite::shouldReceive('driver')->with('github')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($socialiteUser);

    // Call the callback endpoint
    $response = $this->get('/auth/github/callback');

    // Assert user was created
    $this->assertDatabaseHas('users', [
        'github_id' => $githubId,
        'user_name' => $userName,
        'email' => $email,
    ]);

    // Assert redirect to hunts index
    $response->assertRedirect(route('hunts.index'));
});

it('updates an existing user when github user exists', function () {
    $this->withoutExceptionHandling();

    // Create test data
    $githubId = '123456';
    $userName = 'githubuser';
    $email = 'user@example.com';

    // Create a user with the same github_id
    $existingUser = User::factory()->create([
        'github_id' => $githubId,
        'user_name' => 'oldusername',
        'email' => 'old@example.com',
        'bio' => 'Old bio',
        'location' => 'Old location',
        'avatar_url' => 'old-avatar.jpg',
    ]);

    // Create a mock Socialite user
    $socialiteUser = new SocialiteUser();
    $socialiteUser->id = $githubId;
    $socialiteUser->nickname = $userName;
    $socialiteUser->name = 'GitHub User';
    $socialiteUser->email = $email;
    $socialiteUser->avatar = 'https://github.com/avatar.jpg';
    $socialiteUser->token = 'github-token';
    $socialiteUser->refreshToken = 'github-refresh-token';

    // Set raw data
    $raw = [
        'login' => $userName,
        'bio' => 'Developer',
        'location' => 'Earth',
        'html_url' => 'https://github.com/'.$userName,
    ];

    $socialiteUser->setRaw($raw)->map([
        'id' => $githubId,
        'nickname' => $userName,
        'name' => 'GitHub User',
        'email' => $email,
        'avatar' => 'https://github.com/avatar.jpg',
    ]);

    // Mock the Socialite facade
    Socialite::shouldReceive('driver')->with('github')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($socialiteUser);

    // Call the callback endpoint
    $response = $this->get('/auth/github/callback');

    // Assert user was updated
    $this->assertDatabaseHas('users', [
        'id' => $existingUser->id,
        'github_id' => $githubId,
        'user_name' => $userName,
        'email' => 'old@example.com', // Should keep existing email
        'bio' => 'Old bio', // Should keep existing bio
        'location' => 'Old location', // Should keep existing location
        'avatar_url' => 'https://github.com/avatar.jpg', // Should keep existing avatar
    ]);

    // Assert redirect to hunts index
    $response->assertRedirect(route('hunts.index'));
});

it('preserves existing user data when some github fields are null', function () {
    $this->withoutExceptionHandling();

    // Create test data
    $githubId = '123456';
    $userName = 'githubuser';

    // Create a user with the same github_id but with all fields filled
    $existingUser = User::factory()->create([
        'github_id' => $githubId,
        'user_name' => 'oldusername',
        'email' => 'old@example.com',
        'bio' => 'Old bio',
        'location' => 'Old location',
        'avatar_url' => 'old-avatar.jpg',
    ]);

    // Create a mock Socialite user with null values for some fields
    $socialiteUser = new SocialiteUser();
    $socialiteUser->id = $githubId;
    $socialiteUser->nickname = $userName;
    $socialiteUser->name = 'GitHub User';
    $socialiteUser->email = null; // Null email
    $socialiteUser->avatar = null; // Null avatar
    $socialiteUser->token = 'github-token';
    $socialiteUser->refreshToken = 'github-refresh-token';

    // Set raw data with null values
    $raw = [
        'login' => $userName,
        'bio' => null, // Null bio
        'location' => null, // Null location
        'html_url' => 'https://github.com/'.$userName,
    ];

    $socialiteUser->setRaw($raw)->map([
        'id' => $githubId,
        'nickname' => $userName,
        'name' => 'GitHub User',
        'email' => null,
        'avatar' => null,
    ]);

    // Mock the Socialite facade
    Socialite::shouldReceive('driver')->with('github')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($socialiteUser);

    // Call the callback endpoint
    $response = $this->get('/auth/github/callback');

    // Assert user was updated but preserved existing values for null fields
    $this->assertDatabaseHas('users', [
        'id' => $existingUser->id,
        'github_id' => $githubId,
        'user_name' => $userName,
        'email' => 'old@example.com', // Should keep existing email
        'bio' => 'Old bio', // Should keep existing bio
        'location' => 'Old location', // Should keep existing location
        'avatar_url' => null, // Should keep existing avatar
    ]);

    // Assert redirect to hunts index
    $response->assertRedirect(route('hunts.index'));
});

it('prevents linking GitHub account already associated with another user', function () {
    // Create test data
    $githubId = '123456';
    $userName = 'githubuser';
    $email = 'user@example.com';

    // Create first user with GitHub account
    $existingUserWithGithub = User::factory()->create([
        'github_id' => $githubId,
        'user_name' => $userName,
        'email' => 'existing@example.com',
    ]);

    // Create second user (authenticated) trying to link the same GitHub account
    $authenticatedUser = User::factory()->create([
        'github_id' => null,
        'user_name' => 'otheruser',
        'email' => 'authenticated@example.com',
    ]);

    // Authenticate as the second user
    $this->actingAs($authenticatedUser);

    // Create a mock Socialite user
    $socialiteUser = new SocialiteUser();
    $socialiteUser->id = $githubId;
    $socialiteUser->nickname = $userName;
    $socialiteUser->name = 'GitHub User';
    $socialiteUser->email = $email;
    $socialiteUser->avatar = 'https://github.com/avatar.jpg';
    $socialiteUser->token = 'github-token';
    $socialiteUser->refreshToken = 'github-refresh-token';

    // Set raw data
    $raw = [
        'login' => $userName,
        'bio' => 'Developer',
        'location' => 'Earth',
        'html_url' => 'https://github.com/'.$userName,
    ];

    $socialiteUser->setRaw($raw)->map([
        'id' => $githubId,
        'nickname' => $userName,
        'name' => 'GitHub User',
        'email' => $email,
        'avatar' => 'https://github.com/avatar.jpg',
    ]);

    // Mock the Socialite facade
    Socialite::shouldReceive('driver')->with('github')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($socialiteUser);

    // Call the callback endpoint
    $response = $this->get('/auth/github/callback');

    // Assert redirect to security settings with error
    $response->assertRedirect(route('security.index'));
    $response->assertSessionHas('error', 'This GitHub account is already linked to another user.');

    // Assert the authenticated user is still logged in (no account takeover)
    $this->assertAuthenticatedAs($authenticatedUser);

    // Assert the authenticated user's GitHub ID was NOT updated
    $authenticatedUser->refresh();
    expect($authenticatedUser->github_id)->toBeNull();

    // Assert the existing user still has the GitHub account
    $existingUserWithGithub->refresh();
    expect($existingUserWithGithub->github_id)->toBe($githubId);
});
