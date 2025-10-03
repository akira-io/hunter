# Testing

## Overview

Hunter uses **Pest PHP** for testing, providing an elegant and expressive syntax for writing tests. All tests follow best practices and cover critical application functionality.

## Test Structure

```
tests/
├── Feature/            # Feature/integration tests
│   ├── Auth/          # Authentication tests
│   ├── Hunt/          # Hunt-related tests
│   ├── Social/        # Social features tests
│   └── ...
├── Unit/              # Unit tests
│   ├── Actions/       # Action tests
│   ├── Services/      # Service tests
│   └── ...
├── Pest.php           # Pest configuration
└── TestCase.php       # Base test case
```

## Running Tests

### Run All Tests

```bash
php artisan test
```

### Run Specific Test File

```bash
php artisan test tests/Feature/HuntTest.php
```

### Run Tests with Filter

```bash
# Run tests matching a pattern
php artisan test --filter=testUserCanCreateHunt

# Run tests in a specific directory
php artisan test tests/Feature/Auth/
```

### Run with Coverage

```bash
php artisan test --coverage
```

### Parallel Testing

```bash
php artisan test --parallel
```

## Pest Syntax

### Basic Test Structure

```php
<?php

use App\Models\User;
use App\Models\Hunt;

test('user can create a hunt', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->post('/hunts', [
            'content' => 'My first hunt!',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('hunts', [
        'owner_id' => $user->id,
        'content' => 'My first hunt!',
    ]);
});

it('requires authentication to create hunt', function () {
    $this->post('/hunts', [
        'content' => 'Test hunt',
    ])->assertRedirect('/login');
});
```

### Using Datasets

Datasets allow you to run the same test with different inputs:

```php
test('validates hunt content', function (string $content, bool $shouldPass) {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->post('/hunts', ['content' => $content]);
    
    if ($shouldPass) {
        $response->assertRedirect();
    } else {
        $response->assertSessionHasErrors('content');
    }
})->with([
    'valid content' => ['Valid hunt content', true],
    'empty content' => ['', false],
    'too long' => [str_repeat('a', 1001), false],
]);
```

### Hooks

```php
beforeEach(function () {
    // Runs before each test
    $this->user = User::factory()->create();
});

afterEach(function () {
    // Runs after each test
    // Cleanup code
});
```

## Feature Tests

### Authentication Tests

```php
<?php

use App\Models\User;

test('users can register', function () {
    $response = $this->post('/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/');
    $this->assertAuthenticated();
});

test('users can login', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    $this->assertAuthenticatedAs($user);
});

test('users cannot login with invalid credentials', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect('/');

    $this->assertGuest();
});
```

### Hunt Tests

```php
<?php

use App\Models\User;
use App\Models\Hunt;

test('user can create hunt', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post('/hunts', [
            'content' => 'Just deployed my first app!',
        ]);

    $response->assertRedirect();
    
    $this->assertDatabaseHas('hunts', [
        'owner_id' => $user->id,
        'content' => 'Just deployed my first app!',
    ]);
});

test('user can update their own hunt', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

    $response = $this->actingAs($user)
        ->put("/hunts/{$hunt->id}", [
            'content' => 'Updated content',
        ]);

    $response->assertRedirect();
    
    $this->assertEquals('Updated content', $hunt->fresh()->content);
});

test('user cannot update another users hunt', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $otherUser->id]);

    $response = $this->actingAs($user)
        ->put("/hunts/{$hunt->id}", [
            'content' => 'Hacked content',
        ]);

    $response->assertForbidden();
});

test('user can delete their own hunt', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

    $response = $this->actingAs($user)
        ->delete("/hunts/{$hunt->id}");

    $response->assertRedirect();
    
    $this->assertDatabaseMissing('hunts', ['id' => $hunt->id]);
});

test('hunt content is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post('/hunts', [
            'content' => '',
        ]);

    $response->assertSessionHasErrors('content');
});
```

### Social Features Tests

```php
<?php

use App\Models\User;
use App\Models\Hunt;

test('user can follow another user', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    $response = $this->actingAs($user)
        ->post("/follow/{$targetUser->id}");

    $response->assertRedirect();
    
    expect($user->isFollowing($targetUser))->toBeTrue();
});

test('user can unfollow another user', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();
    
    $user->follow($targetUser);

    $response = $this->actingAs($user)
        ->post("/unfollow/{$targetUser->id}");

    $response->assertRedirect();
    
    expect($user->isFollowing($targetUser))->toBeFalse();
});

test('user can like a hunt', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create();

    $response = $this->actingAs($user)
        ->post("/hunts/{$hunt->id}/like");

    $response->assertRedirect();
    
    expect($user->hasLiked($hunt))->toBeTrue();
});

test('user can unlike a hunt', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create();
    
    $user->like($hunt);

    $response = $this->actingAs($user)
        ->post("/hunts/{$hunt->id}/like");

    expect($user->hasLiked($hunt))->toBeFalse();
});

test('user can comment on hunt', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create();

    $response = $this->actingAs($user)
        ->post("/hunts/{$hunt->id}/comments", [
            'content' => 'Great post!',
        ]);

    $response->assertRedirect();
    
    $this->assertDatabaseHas('comments', [
        'user_id' => $user->id,
        'commentable_id' => $hunt->id,
        'content' => 'Great post!',
    ]);
});
```

### Chat Tests

```php
<?php

use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;

test('user can create conversation', function () {
    $creator = User::factory()->create();
    $participant = User::factory()->create();

    $response = $this->actingAs($creator)
        ->post('/conversations', [
            'participant_ids' => [$participant->id],
            'message' => 'Hello!',
        ]);

    $response->assertRedirect();
    
    $this->assertDatabaseHas('conversations', [
        'created_by' => $creator->id,
    ]);
});

test('user can send message', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();
    $conversation->participants()->attach($user->id);

    $response = $this->actingAs($user)
        ->post('/messages', [
            'conversation_id' => $conversation->id,
            'content' => 'Test message',
        ]);

    $response->assertRedirect();
    
    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'content' => 'Test message',
    ]);
});

test('user can only send message to their conversations', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();

    $response = $this->actingAs($user)
        ->post('/messages', [
            'conversation_id' => $conversation->id,
            'content' => 'Test message',
        ]);

    $response->assertForbidden();
});
```

## Unit Tests

### Action Tests

```php
<?php

use App\Actions\Hunt\CreateHuntAction;
use App\Models\User;

test('create hunt action creates hunt', function () {
    $user = User::factory()->create();
    
    $hunt = app(CreateHuntAction::class)->handle([
        'owner_id' => $user->id,
        'content' => 'Test hunt',
    ]);

    expect($hunt)->not->toBeNull();
    expect($hunt->content)->toBe('Test hunt');
    expect($hunt->owner_id)->toBe($user->id);
});

test('create hunt action validates content', function () {
    $user = User::factory()->create();
    
    expect(fn() => app(CreateHuntAction::class)->handle([
        'owner_id' => $user->id,
        'content' => '',
    ]))->toThrow(ValidationException::class);
});
```

### Service Tests

```php
<?php

use App\Services\Search\UserSearchService;
use App\Models\User;

test('search service finds users by name', function () {
    User::factory()->create(['name' => 'John Laravel Developer']);
    User::factory()->create(['name' => 'Jane React Developer']);

    $service = app(UserSearchService::class);
    $results = $service->search(['query' => 'laravel']);

    expect($results)->toHaveCount(1);
    expect($results->first()->name)->toContain('Laravel');
});
```

## Database Testing

### Using Factories

```php
use App\Models\User;
use App\Models\Hunt;

test('factories create valid models', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

    expect($user->exists)->toBeTrue();
    expect($hunt->owner_id)->toBe($user->id);
});

test('factories support states', function () {
    $verifiedUser = User::factory()->verified()->create();
    $unverifiedUser = User::factory()->unverified()->create();

    expect($verifiedUser->email_verified_at)->not->toBeNull();
    expect($unverifiedUser->email_verified_at)->toBeNull();
});
```

### Database Transactions

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('test runs in transaction', function () {
    // Database changes are rolled back after test
    User::factory()->create();
    
    // This won't affect other tests
});
```

## API Testing

```php
test('api returns user data', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->getJson('/api/user');

    $response->assertOk()
        ->assertJson([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
});

test('api requires authentication', function () {
    $response = $this->getJson('/api/user');

    $response->assertUnauthorized();
});

test('api validates input', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/hunts', [
            'content' => '', // Invalid
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['content']);
});
```

## Mocking

### Mocking Services

```php
use App\Services\NotificationService;

test('sends notification when user is followed', function () {
    $mock = mock(NotificationService::class);
    $mock->shouldReceive('sendFollowNotification')
        ->once()
        ->with(Mockery::type(User::class));

    $user = User::factory()->create();
    $follower = User::factory()->create();

    $follower->follow($user);
});
```

### Mocking External APIs

```php
use Illuminate\Support\Facades\Http;

test('handles github api response', function () {
    Http::fake([
        'api.github.com/*' => Http::response([
            'login' => 'johndoe',
            'name' => 'John Doe',
            'avatar_url' => 'https://...',
        ], 200),
    ]);

    // Test code that calls GitHub API
});
```

## Test Helpers

### Custom Assertions

```php
// tests/Pest.php
expect()->extend('toBeValidEmail', function () {
    return $this->toMatch('/^[^@]+@[^@]+\.[^@]+$/');
});

// Usage
test('email format is valid', function () {
    $email = 'user@example.com';
    expect($email)->toBeValidEmail();
});
```

### Helper Functions

```php
// tests/Pest.php
function createAuthenticatedUser(): User
{
    $user = User::factory()->create();
    test()->actingAs($user);
    return $user;
}

// Usage
test('authenticated user can access dashboard', function () {
    $user = createAuthenticatedUser();
    
    $this->get('/dashboard')->assertOk();
});
```

## Browser Testing (Pest 4)

Hunter can use Pest 4 browser testing for end-to-end tests:

```php
<?php

use function Pest\Laravel\browse;

test('user can login through browser', function () {
    browse(function ($browser) {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $browser->visit('/login')
            ->type('email', $user->email)
            ->type('password', 'password')
            ->press('Login')
            ->assertPathIs('/')
            ->assertAuthenticated();
    });
});

test('user can create hunt through browser', function () {
    browse(function ($browser) {
        $user = User::factory()->create();

        $browser->loginAs($user)
            ->visit('/')
            ->type('textarea[name="content"]', 'My first hunt!')
            ->press('Post')
            ->assertSee('My first hunt!');
    });
});
```

## Continuous Integration

### GitHub Actions

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  tests:
    runs-on: ubuntu-latest

    services:
      postgres:
        image: postgres:14
        env:
          POSTGRES_PASSWORD: postgres
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5

    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.4
          extensions: pdo, pgsql
      
      - name: Install Dependencies
        run: composer install
      
      - name: Run Tests
        run: php artisan test
        env:
          DB_CONNECTION: pgsql
          DB_HOST: localhost
          DB_PORT: 5432
          DB_DATABASE: testing
          DB_USERNAME: postgres
          DB_PASSWORD: postgres
```

## Best Practices

1. **Use descriptive test names** - Tests should read like documentation
2. **One assertion per test** - Keep tests focused
3. **Use factories** - Don't create models manually
4. **Clean up after tests** - Use RefreshDatabase trait
5. **Mock external services** - Don't hit real APIs
6. **Test edge cases** - Don't just test happy paths
7. **Keep tests fast** - Use databases efficiently
8. **Test behavior, not implementation** - Focus on what, not how
9. **Use datasets** - Reduce code duplication
10. **Run tests frequently** - Catch issues early

## Code Coverage

Generate coverage reports:

```bash
# HTML coverage report
php artisan test --coverage-html coverage/

# Text coverage report
php artisan test --coverage
```

Aim for:
- **80%+ overall coverage**
- **100% coverage** for critical features (auth, payments, etc.)
- **Unit tests** for complex business logic
- **Feature tests** for user-facing functionality
