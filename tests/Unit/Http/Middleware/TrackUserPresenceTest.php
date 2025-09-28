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
    $next = fn ($request) => response('OK');

    $response = $middleware->handle($request, $next);

    expect($response->getContent())->toBe('OK');
});

test('handle when auth user is not User instance', function () {
    // Este é um teste complexo pois precisamos simular um caso onde
    // Auth::check() retorna true mas Auth::user() não retorna User instance
    // Vamos usar uma abordagem criativa: criar um stub de auth guard personalizado

    $customUser = new stdClass();
    $customUser->id = 123;

    // Simular um guard personalizado que retorna um objeto que não é User
    $request = Request::create('/', 'GET');
    $request->setUserResolver(function () use ($customUser) {
        return $customUser; // Retorna stdClass ao invés de User
    });

    $middleware = new TrackUserPresence();
    $next = fn ($request) => response('middleware executed for non-user');

    $response = $middleware->handle($request, $next);

    expect($response->getContent())->toBe('middleware executed for non-user');
});

test('handle when user id is not numeric using custom user resolver', function () {
    // Criar um user customizado que retorna ID não numérico
    $user = User::factory()->create();

    // Simular um cenário onde getAttribute('id') retorna string não numérica
    $request = Request::create('/', 'GET');
    $request->setUserResolver(function () use ($user) {
        // Criar um objeto que se comporta como User mas com ID não numérico
        $customUser = new class($user->getAttributes()) extends User {
            private array $customAttributes;

            public function __construct(array $attributes) {
                parent::__construct();
                $this->customAttributes = $attributes;
            }

            public function getAttribute($key) {
                if ($key === 'id') {
                    return 'string-id-not-numeric'; // ID não numérico
                }
                return $this->customAttributes[$key] ?? null;
            }
        };

        return $customUser;
    });

    $middleware = new TrackUserPresence();
    $next = fn ($request) => response('middleware executed with non-numeric id');

    $response = $middleware->handle($request, $next);

    expect($response->getContent())->toBe('middleware executed with non-numeric id');
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
