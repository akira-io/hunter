<?php

declare(strict_types=1);

use App\Events\ConversationsSnapshot;
use App\Events\UserOnline;
use App\Http\Middleware\TrackUserPresence;
use App\Models\User;
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

test('handle when auth user is not User instance', function () {
    // Simular um guard personalizado que retorna um objeto que não é User
    $customUser = new stdClass();
    $customUser->id = 123;

    $request = Request::create('/', 'GET');
    $request->setUserResolver(function () use ($customUser) {
        return $customUser; // Retorna stdClass ao invés de User
    });

    $middleware = new TrackUserPresence();

    // Esta closure deve forçar a execução das linhas 27-29
    $nextCalled = false;
    $next = function ($request) use (&$nextCalled) {
        $nextCalled = true;

        return response('middleware executed for non-user');
    };

    $response = $middleware->handle($request, $next);

    expect($response->getContent())->toBe('middleware executed for non-user')
        ->and($nextCalled)->toBeTrue(); // Confirma que $next foi chamado
});

test('handle when user id is not numeric using custom user resolver', function () {
    // Criar um user customizado que retorna ID não numérico
    $user = User::factory()->create();

    // Simular um cenário onde getAttribute('id') retorna string não numérica
    $request = Request::create('/', 'GET');
    $request->setUserResolver(function () use ($user) {
        // Criar um objeto que se comporta como User mas com ID não numérico
        $customUser = new class($user->getAttributes()) extends User
        {
            private array $customAttributes;

            public function __construct(array $attributes)
            {
                parent::__construct();
                $this->customAttributes = $attributes;
            }

            public function getAttribute($key)
            {
                if ($key === 'id') {
                    return 'string-id-not-numeric'; // ID não numérico
                }

                return $this->customAttributes[$key] ?? null;
            }
        };

        return $customUser;
    });

    $middleware = new TrackUserPresence();

    // Esta closure deve forçar a execução das linhas 35-37
    $nextCalled = false;
    $next = function ($request) use (&$nextCalled) {
        $nextCalled = true;

        return response('middleware executed with non-numeric id');
    };

    $response = $middleware->handle($request, $next);

    expect($response->getContent())->toBe('middleware executed with non-numeric id')
        ->and($nextCalled)->toBeTrue(); // Confirma que $next foi chamado
});

test('handle when user is valid and cache is empty', function () {
    Event::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    // Clear cache to ensure it's empty
    Cache::forget("user_online_{$user->id}");

    $middleware = new TrackUserPresence();
    $request = Request::create('/', 'GET');
    $next = fn ($request) => response('OK');

    $response = $middleware->handle($request, $next);

    expect($response->getContent())->toBe('OK');
    Event::assertDispatched(UserOnline::class);
    Event::assertDispatched(ConversationsSnapshot::class);

    // Verify cache was set
    expect(Cache::has("user_online_{$user->id}"))->toBeTrue();
});

test('handle when user is valid and cache exists', function () {
    Event::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    // Pre-populate cache
    Cache::put("user_online_{$user->id}", now());

    $middleware = new TrackUserPresence();
    $request = Request::create('/', 'GET');
    $next = fn ($request) => response('OK');

    $response = $middleware->handle($request, $next);

    expect($response->getContent())->toBe('OK');
    Event::assertNotDispatched(UserOnline::class);
    Event::assertNotDispatched(ConversationsSnapshot::class);
});

// EXCEPTIONAL CASE: Using minimal mocking ONLY for 100% coverage of defensive code
// These specific lines (27-29, 35-37) are impossible to reach without mocking
// due to Laravel's type system and authentication constraints

test('covers lines 27-29 using minimal required mocking for defensive code', function () {
    // This is the ONLY way to reach these specific defensive lines
    Auth::shouldReceive('check')->once()->andReturn(true);
    Auth::shouldReceive('user')->once()->andReturn((object) ['id' => 123]);

    $middleware = new TrackUserPresence();
    $request = Request::create('/', 'GET');

    $executed = false;
    $response = response('lines-27-29-covered');
    $next = function ($req) use (&$executed, $response) {
        $executed = true;

        return $response;
    };

    $result = $middleware->handle($request, $next);

    expect($executed)->toBeTrue()->and($result)->toBe($response);
});

test('covers lines 35-37 using reflection to modify User attributes', function () {
    $user = User::factory()->create();

    // Use reflection to modify the user's attributes to have non-numeric ID
    $reflection = new ReflectionClass($user);
    $attributesProperty = $reflection->getProperty('attributes');
    $attributesProperty->setAccessible(true);
    $attributes = $attributesProperty->getValue($user);
    $attributes['id'] = 'string-not-numeric'; // Force non-numeric ID
    $attributesProperty->setValue($user, $attributes);

    // Mock Auth facade
    Auth::shouldReceive('check')->once()->andReturn(true);
    Auth::shouldReceive('user')->once()->andReturn($user);

    $middleware = new TrackUserPresence();
    $request = Request::create('/', 'GET');

    $executed = false;
    $response = response('lines-35-37-covered');
    $next = function ($req) use (&$executed, $response) {
        $executed = true;

        return $response;
    };

    $result = $middleware->handle($request, $next);

    expect($executed)->toBeTrue()->and($result)->toBe($response);
});
