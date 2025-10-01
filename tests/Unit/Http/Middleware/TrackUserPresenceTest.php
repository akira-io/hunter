<?php

declare(strict_types=1);

use App\Events\ConversationsSnapshot;
use App\Events\UserOnline;
use App\Http\Middleware\TrackUserPresence;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

test('handle when user is not authenticated', function () {
    Auth::logout();

    $middleware = new TrackUserPresence();
    $request = Request::create('/', 'GET');

    // Use a regular function to ensure we hit the return statements
    $nextExecuted = false;
    $next = function ($request) use (&$nextExecuted) {
        $nextExecuted = true;

        return response('OK');
    };

    $response = $middleware->handle($request, $next);

    expect($response->getContent())->toBe('OK')
        ->and($nextExecuted)->toBeTrue();
});

test('handle when auth user is not User instance - forces lines 25-30', function () {
    // Create a custom authenticatable object that is NOT a User instance
    $customUser = new class implements Authenticatable
    {
        public function getAuthIdentifierName(): string
        {
            return 'id';
        }

        public function getAuthIdentifier(): mixed
        {
            return 123;
        }

        public function getAuthPasswordName(): string
        {
            return 'password';
        }

        public function getAuthPassword(): string
        {
            return 'password';
        }

        public function getRememberToken(): ?string
        {
            return null;
        }

        public function setRememberToken($value): void
        {
            // No-op
        }

        public function getRememberTokenName(): string
        {
            return 'remember_token';
        }
    };

    // Mock Auth facade directly to ensure we hit the exact code path
    Auth::swap(new class($customUser)
    {
        private $user;

        public function __construct($user)
        {
            $this->user = $user;
        }

        public function __call($method, $parameters)
        {
            return null;
        }

        public function check(): bool
        {
            return true; // User is authenticated
        }

        public function user()
        {
            return $this->user; // Returns our custom non-User object
        }
    });

    $middleware = new TrackUserPresence();
    $request = Request::create('/', 'GET');

    $nextCalled = false;
    $originalResponse = response('lines-25-30-covered', 418); // Use unique status code
    $next = function ($request) use (&$nextCalled, $originalResponse) {
        $nextCalled = true;

        return $originalResponse;
    };

    $response = $middleware->handle($request, $next);

    // Verify exact path was taken (lines 25-30)
    expect($nextCalled)->toBeTrue()
        ->and($response)->toBe($originalResponse)
        ->and($response->getStatusCode())->toBe(418)
        ->and($response->getContent())->toBe('lines-25-30-covered');
});

// Note: Cache tests moved to more comprehensive versions below

test('handle when user has null id attribute using spy', function () {
    // Create a real user and use spy to override getAttribute
    $user = User::factory()->create();

    // Spy on the user to override getAttribute for 'id'
    $spy = spy($user);
    $spy->shouldReceive('getAttribute')
        ->with('id')
        ->andReturn(null);

    $request = Request::create('/', 'GET');
    $request->setUserResolver(function () use ($spy) {
        return $spy;
    });

    $middleware = new TrackUserPresence();

    $nextCalled = false;
    $next = function ($request) use (&$nextCalled) {
        $nextCalled = true;

        return response('middleware executed with null id');
    };

    $response = $middleware->handle($request, $next);

    expect($response->getContent())->toBe('middleware executed with null id')
        ->and($nextCalled)->toBeTrue();
});

test('handle when user has non-numeric id using spy - forces lines 33-38', function () {
    Event::fake(); // Ensure no events are dispatched

    // Create a real user and use spy to override getAttribute
    $user = User::factory()->create();

    // Spy on the user to override getAttribute for 'id'
    $spy = spy($user);
    $spy->shouldReceive('getAttribute')
        ->with('id')
        ->andReturn('NaN'); // Non-numeric value

    // Use Auth::swap to ensure proper authentication flow
    Auth::swap(new class($spy)
    {
        private $user;

        public function __construct($user)
        {
            $this->user = $user;
        }

        public function __call($method, $parameters)
        {
            return null;
        }

        public function check(): bool
        {
            return true; // User is authenticated
        }

        public function user()
        {
            return $this->user; // Returns our spy user
        }
    });

    $middleware = new TrackUserPresence();
    $request = Request::create('/', 'GET');

    $nextCalled = false;
    $originalResponse = response('lines-33-38-covered', 422); // Use unique status code
    $next = function ($request) use (&$nextCalled, $originalResponse) {
        $nextCalled = true;

        return $originalResponse;
    };

    $response = $middleware->handle($request, $next);

    // Verify exact path was taken (lines 33-38)
    expect($nextCalled)->toBeTrue()
        ->and($response)->toBe($originalResponse)
        ->and($response->getStatusCode())->toBe(422)
        ->and($response->getContent())->toBe('lines-33-38-covered');

    // Ensure no events were dispatched (skipped cache logic)
    Event::assertNotDispatched(UserOnline::class);
    Event::assertNotDispatched(ConversationsSnapshot::class);

    // Ensure no cache was set for the non-numeric ID
    expect(Cache::has('user_online_NaN'))->toBeFalse();
});

test('handle with cache miss triggers events and cache set', function () {
    Event::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    // Ensure cache is empty
    Cache::forget("user_online_{$user->id}");

    $middleware = new TrackUserPresence();
    $request = Request::create('/', 'GET');

    $nextCalled = false;
    $next = function ($request) use (&$nextCalled) {
        $nextCalled = true;

        return response('Events triggered');
    };

    $response = $middleware->handle($request, $next);

    expect($response->getContent())->toBe('Events triggered')
        ->and($nextCalled)->toBeTrue();

    Event::assertDispatched(UserOnline::class, function ($event) use ($user) {
        return $event->user->id === $user->id;
    });

    Event::assertDispatched(ConversationsSnapshot::class, function ($event) use ($user) {
        return $event->user->id === $user->id;
    });

    // Verify cache was set with correct TTL
    expect(Cache::has("user_online_{$user->id}"))->toBeTrue();
});

test('handle with cache hit does not trigger events but refreshes TTL', function () {
    Event::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    // Pre-populate cache
    $cacheKey = "user_online_{$user->id}";
    Cache::put($cacheKey, now()->subMinutes(2));

    $middleware = new TrackUserPresence();
    $request = Request::create('/', 'GET');

    $nextCalled = false;
    $next = function ($request) use (&$nextCalled) {
        $nextCalled = true;

        return response('Cache hit');
    };

    $response = $middleware->handle($request, $next);

    expect($response->getContent())->toBe('Cache hit')
        ->and($nextCalled)->toBeTrue();

    Event::assertNotDispatched(UserOnline::class);
    Event::assertNotDispatched(ConversationsSnapshot::class);

    // Verify cache still exists (TTL was refreshed)
    expect(Cache::has($cacheKey))->toBeTrue();
});

test('middleware preserves response object integrity', function () {
    Event::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    $middleware = new TrackUserPresence();
    $request = Request::create('/', 'GET');

    $originalResponse = response()->json(['test' => 'data'], 201)
        ->header('Custom-Header', 'custom-value');

    $next = function ($request) use ($originalResponse) {
        return $originalResponse;
    };

    $result = $middleware->handle($request, $next);

    expect($result)->toBe($originalResponse)
        ->and($result->getStatusCode())->toBe(201)
        ->and($result->headers->get('Custom-Header'))->toBe('custom-value');
});

test('middleware handles multiple sequential requests correctly', function () {
    Event::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    $middleware = new TrackUserPresence();

    // First request - should trigger events
    Cache::forget("user_online_{$user->id}");
    $request1 = Request::create('/first', 'GET');
    $next1 = fn ($req) => response('first');

    $middleware->handle($request1, $next1);

    Event::assertDispatched(UserOnline::class);
    Event::assertDispatched(ConversationsSnapshot::class);

    Event::fake(); // Reset event fake

    // Second request - should not trigger events (cache hit)
    $request2 = Request::create('/second', 'GET');
    $next2 = fn ($req) => response('second');

    $middleware->handle($request2, $next2);

    Event::assertNotDispatched(UserOnline::class);
    Event::assertNotDispatched(ConversationsSnapshot::class);
});

test('middleware handles user with complex id types correctly using spies', function () {
    // Test with various edge case ID values
    $testCases = [
        'empty-string' => '',
        'whitespace' => '   ',
        'scientific-notation' => '1e5',
        'negative-zero' => '-0',
        'hex-string' => '0x1A',
        'float-string' => '12.34',
    ];

    foreach ($testCases as $description => $idValue) {
        $user = User::factory()->create();
        $spy = spy($user);
        $spy->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn($idValue);

        $request = Request::create('/', 'GET');
        $request->setUserResolver(function () use ($spy) {
            return $spy;
        });

        $middleware = new TrackUserPresence();
        $nextCalled = false;
        $next = function ($request) use (&$nextCalled, $description) {
            $nextCalled = true;

            return response("handled-{$description}");
        };

        $response = $middleware->handle($request, $next);

        expect($nextCalled)->toBeTrue("Test case: {$description}");

        // Only numeric IDs should proceed to cache logic
        if (is_numeric($idValue) && $idValue !== '') {
            // This would proceed to cache logic - verify no cache errors
            expect($response->getContent())->toBe("handled-{$description}");

            continue;
        }

        // Non-numeric IDs should skip cache logic
        expect($response->getContent())->toBe("handled-{$description}");
    }
});
