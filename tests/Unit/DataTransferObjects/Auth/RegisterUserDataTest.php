<?php

declare(strict_types=1);

use App\DataTransferObjects\Auth\RegisterUserData;
use App\Http\Requests\Auth\RegisterRequest;

test('it can be created with valid data', function () {
    $data = RegisterUserData::fromArray([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    expect($data->name)->toBe('John Doe')
        ->and($data->email)->toBe('john@example.com')
        ->and($data->password)->toBe('password123');
});

test('it can be created directly with constructor', function () {
    $data = new RegisterUserData(
        name: 'Jane Smith',
        email: 'jane@example.com',
        password: 'secret456'
    );

    expect($data->name)->toBe('Jane Smith')
        ->and($data->email)->toBe('jane@example.com')
        ->and($data->password)->toBe('secret456');
});

test('it can be created from register request', function () {
    $request = RegisterRequest::create('/register', 'POST', [
        'name' => 'Alice Johnson',
        'email' => 'alice@example.com',
        'password' => 'password789',
        'password_confirmation' => 'password789',
    ]);

    $data = RegisterUserData::fromRequest($request);

    expect($data->name)->toBe('Alice Johnson')
        ->and($data->email)->toBe('alice@example.com')
        ->and($data->password)->toBe('password789');
});
