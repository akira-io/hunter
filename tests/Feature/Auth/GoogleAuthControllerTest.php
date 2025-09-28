<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\Fixtures\TestSocialiteFactory;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Configure Google OAuth
    config(['services.google.client_id' => 'fake-client-id']);
    config(['services.google.client_secret' => 'fake-client-secret']);
    config(['services.google.redirect' => 'http://localhost/auth/google/callback']);
});

it('redirects to google', function () {
    // Bind our custom factory to the service container
    $this->app->instance(SocialiteFactory::class, new TestSocialiteFactory());

    $response = $this->get('/auth/google');

    $response->assertRedirect('https://accounts.google.com/o/oauth2/auth');
});

it('creates a new user when google user does not exist', function () {
    // Create test data
    $googleId = '123456';
    $name = 'Google User';
    $email = 'user@example.com';
    $avatar = 'https://google.com/avatar.jpg';

    // Create a real Socialite User
    $socialiteUser = new SocialiteUser();
    $socialiteUser->id = $googleId;
    $socialiteUser->name = $name;
    $socialiteUser->email = $email;
    $socialiteUser->avatar = $avatar;
    $socialiteUser->token = 'google-token';
    $socialiteUser->refreshToken = 'google-refresh-token';

    // Bind our custom factory to the service container
    $this->app->instance(SocialiteFactory::class, new TestSocialiteFactory($socialiteUser));

    // Call the callback endpoint
    $response = $this->get('/auth/google/callback');

    // Assert user was created
    $this->assertDatabaseHas('users', [
        'email' => $email,
        'name' => $name,
    ]);

    // Assert user has the correct data
    $user = User::where('email', $email)->first();
    $this->assertNotNull($user);
    $this->assertEquals($name, $user->name);
    $this->assertEquals($avatar, $user->avatar_url);
    $this->assertNotNull($user->password);
    $this->assertNotNull($user->email_verified_at);

    // Assert user is logged in
    $this->assertTrue(auth()->check());
    $this->assertEquals($user->id, auth()->id());

    // Assert redirect to hunts index
    $response->assertRedirect(route('hunts.index'));
});

it('logs in existing user when google user exists', function () {
    // Create test data
    $googleId = '123456';
    $name = 'Google User';
    $email = 'user@example.com';
    $avatar = 'https://google.com/avatar.jpg';

    // Create an existing user
    $existingUser = User::factory()->create([
        'email' => $email,
        'name' => 'Existing User',
        'avatar_url' => 'existing-avatar.jpg',
    ]);

    // Create a real Socialite User
    $socialiteUser = new SocialiteUser();
    $socialiteUser->id = $googleId;
    $socialiteUser->name = $name;
    $socialiteUser->email = $email;
    $socialiteUser->avatar = $avatar;
    $socialiteUser->token = 'google-token';
    $socialiteUser->refreshToken = 'google-refresh-token';

    // Bind our custom factory to the service container
    $this->app->instance(SocialiteFactory::class, new TestSocialiteFactory($socialiteUser));

    // Call the callback endpoint
    $response = $this->get('/auth/google/callback');

    // Assert user was not created again
    $this->assertEquals(1, User::where('email', $email)->count());

    // Assert user is updated with new data
    $user = User::where('email', $email)->first();
    $this->assertNotNull($user);
    $this->assertEquals('Existing User', $user->name); // Name should not be updated
    $this->assertEquals(null, $user->avatar_url); // Avatar should not be updated

    // Assert user is logged in
    $this->assertTrue(auth()->check());
    $this->assertEquals($user->id, auth()->id());

    // Assert redirect to hunts index
    $response->assertRedirect(route('hunts.index'));
});

it('handles null avatar from google', function () {
    // Create test data
    $googleId = '123456';
    $name = 'Google User'; // Name is not null
    $email = 'user@example.com';
    $avatar = null; // Avatar is null

    // Create a real Socialite User with null avatar
    $socialiteUser = new SocialiteUser();
    $socialiteUser->id = $googleId;
    $socialiteUser->name = $name;
    $socialiteUser->email = $email;
    $socialiteUser->avatar = $avatar;
    $socialiteUser->token = 'google-token';
    $socialiteUser->refreshToken = 'google-refresh-token';

    // Bind our custom factory to the service container
    $this->app->instance(SocialiteFactory::class, new TestSocialiteFactory($socialiteUser));

    // Call the callback endpoint
    $response = $this->get('/auth/google/callback');

    // Assert user was created
    $this->assertDatabaseHas('users', [
        'email' => $email,
        'name' => $name,
    ]);

    // Assert user has default values for null fields
    $user = User::where('email', $email)->first();
    $this->assertNotNull($user);
    $this->assertEquals($name, $user->name);
    $this->assertNull($user->avatar_url); // Avatar should be null if avatar is null

    // Assert user is logged in
    $this->assertTrue(auth()->check());
    $this->assertEquals($user->id, auth()->id());

    // Assert redirect to hunts index
    $response->assertRedirect(route('hunts.index'));
});
