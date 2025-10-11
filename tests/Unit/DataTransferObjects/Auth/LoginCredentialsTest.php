<?php

declare(strict_types=1);

use App\DataTransferObjects\Auth\LoginCredentials;

test('it can be created with credentials and defaults', function () {
    $credentials = LoginCredentials::from([
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    expect($credentials->email)->toBe('test@example.com')
        ->and($credentials->password)->toBe('password123')
        ->and($credentials->remember)->toBeFalse()
        ->and($credentials->ip)->toBeNull();
});

test('it can be created with remember and ip parameters', function () {
    $credentials = LoginCredentials::from([
        'email' => 'user@example.com',
        'password' => 'secret',
    ], true, '192.168.1.1');

    expect($credentials->remember)->toBeTrue()
        ->and($credentials->ip)->toBe('192.168.1.1');
});

test('it converts to array for auth attempt', function () {
    $credentials = LoginCredentials::from([
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $array = $credentials->toArray();

    expect($array)->toBe([
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);
});
