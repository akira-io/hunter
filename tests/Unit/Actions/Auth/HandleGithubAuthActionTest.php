<?php

declare(strict_types=1);

use App\Actions\Auth\HandleGithubAuthAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = new HandleGithubAuthAction();
});

it('creates a new user when no existing user is found', function () {
    // Arrange
    $githubUser = new SocialiteUser();
    $githubUser->id = 'github-123';
    $githubUser->nickname = 'johndoe';
    $githubUser->name = 'John Doe';
    $githubUser->email = 'john@example.com';
    $githubUser->avatar = 'https://github.com/avatar.jpg';
    $githubUser->token = 'github-token';
    $githubUser->refreshToken = 'github-refresh-token';

    $githubUser->setRaw([
        'login' => 'johndoe',
        'bio' => 'Developer',
        'location' => 'Earth',
        'html_url' => 'https://github.com/johndoe',
    ]);

    // Act
    $user = $this->action->handle($githubUser);

    // Assert
    expect($user)->toBeInstanceOf(User::class);
    expect($user->email)->toBe('john@example.com');
    expect($user->github_id)->toBe('github-123');
    expect($user->user_name)->toBe('johndoe');
    expect($user->bio)->toBe('Developer');
    expect($user->location)->toBe('Earth');

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'github_id' => 'github-123',
        'user_name' => 'johndoe',
    ]);
});

it('links GitHub account to existing user found by email', function () {
    // Arrange - Create existing user (e.g., from Google auth)
    $existingUser = User::factory()->create([
        'email' => 'john@example.com',
        'name' => 'John Doe',
        'github_id' => null,
        'bio' => null,
        'location' => null,
    ]);

    $githubUser = new SocialiteUser();
    $githubUser->id = 'github-123';
    $githubUser->nickname = 'johndoe';
    $githubUser->name = 'John Doe (GitHub)';
    $githubUser->email = 'john@example.com';
    $githubUser->avatar = 'https://github.com/avatar.jpg';
    $githubUser->token = 'github-token';
    $githubUser->refreshToken = 'github-refresh-token';

    $githubUser->setRaw([
        'login' => 'johndoe',
        'bio' => 'Developer from GitHub',
        'location' => 'GitHub Land',
        'html_url' => 'https://github.com/johndoe',
    ]);

    // Act
    $user = $this->action->handle($githubUser);

    // Assert
    expect($user->id)->toBe($existingUser->id); // Same user
    expect($user->email)->toBe('john@example.com'); // Email preserved
    expect($user->name)->toBe('John Doe'); // Original name preserved
    expect($user->github_id)->toBe('github-123'); // GitHub ID added
    expect($user->user_name)->toBe('johndoe'); // GitHub username added
    expect($user->bio)->toBe('Developer from GitHub'); // Bio updated (was null)
    expect($user->location)->toBe('GitHub Land'); // Location updated (was null)

    // Verify only one user with this email exists
    expect(User::where('email', 'john@example.com')->count())->toBe(1);
});

it('preserves existing user data when linking GitHub account', function () {
    // Arrange - Create existing user with existing data
    $existingUser = User::factory()->create([
        'email' => 'john@example.com',
        'name' => 'John Doe',
        'github_id' => null,
        'bio' => 'Existing bio',
        'location' => 'Existing location',
        'avatar_url' => 'https://existing-avatar.jpg',
    ]);

    $githubUser = new SocialiteUser();
    $githubUser->id = 'github-123';
    $githubUser->nickname = 'johndoe';
    $githubUser->name = 'John Doe (GitHub)';
    $githubUser->email = 'john@example.com';
    $githubUser->avatar = 'https://github.com/avatar.jpg';
    $githubUser->token = 'github-token';
    $githubUser->refreshToken = 'github-refresh-token';

    $githubUser->setRaw([
        'login' => 'johndoe',
        'bio' => 'New bio from GitHub',
        'location' => 'New location from GitHub',
        'html_url' => 'https://github.com/johndoe',
    ]);

    // Act
    $user = $this->action->handle($githubUser);

    // Assert
    expect($user->id)->toBe($existingUser->id);
    expect($user->bio)->toBe('Existing bio'); // Existing data preserved
    expect($user->location)->toBe('Existing location'); // Existing data preserved
    expect($user->avatar_url)->toBe('https://existing-avatar.jpg'); // Existing data preserved
    expect($user->github_id)->toBe('github-123'); // GitHub data added
    expect($user->user_name)->toBe('johndoe'); // GitHub data added
});

it('updates existing GitHub user found by github_id', function () {
    // Arrange - Create existing GitHub user
    $existingUser = User::factory()->create([
        'email' => 'john@example.com',
        'name' => 'John Doe',
        'github_id' => 'github-123',
        'user_name' => 'oldusername',
        'bio' => 'Old bio',
        'location' => 'Old location',
    ]);

    $githubUser = new SocialiteUser();
    $githubUser->id = 'github-123';
    $githubUser->nickname = 'newusername';
    $githubUser->name = 'John Doe Updated';
    $githubUser->email = 'john@example.com';
    $githubUser->avatar = 'https://github.com/new-avatar.jpg';
    $githubUser->token = 'new-github-token';
    $githubUser->refreshToken = 'new-github-refresh-token';

    $githubUser->setRaw([
        'login' => 'newusername',
        'bio' => null, // New bio is null
        'location' => null, // New location is null
        'html_url' => 'https://github.com/newusername',
    ]);

    // Act
    $user = $this->action->handle($githubUser);

    // Assert
    expect($user->id)->toBe($existingUser->id);
    expect($user->user_name)->toBe('newusername'); // Updated
    expect($user->bio)->toBe('Old bio'); // Preserved (new is null)
    expect($user->location)->toBe('Old location'); // Preserved (new is null)
    expect($user->email)->toBe('john@example.com'); // Preserved
    expect($user->github_token)->toBe('new-github-token'); // Updated
});
