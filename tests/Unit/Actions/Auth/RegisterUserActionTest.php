<?php

declare(strict_types=1);

use App\Actions\Auth\RegisterUserAction;
use App\DataTransferObjects\Auth\RegisterUserData;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

test('it can register a new user', function () {
    Event::fake();

    $action = new RegisterUserAction();
    $userData = RegisterUserData::fromArray([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $user = $action->handle($userData);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('John Doe')
        ->and($user->email)->toBe('john@example.com')
        ->and(Hash::check('password123', $user->password))->toBeTrue();

    Event::assertDispatched(Registered::class, function ($event) use ($user) {
        return $event->user->id === $user->id;
    });
});
