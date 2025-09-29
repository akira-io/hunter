<?php

declare(strict_types=1);

use App\DataTransferObjects\Auth\PasswordResetData;
use App\Http\Requests\Auth\NewPasswordRequest;

test('it can be created from array data', function () {
    $data = PasswordResetData::fromArray([
        'token' => 'reset-token-123',
        'email' => 'user@example.com',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    expect($data->token)->toBe('reset-token-123')
        ->and($data->email)->toBe('user@example.com')
        ->and($data->password)->toBe('newpassword123')
        ->and($data->passwordConfirmation)->toBe('newpassword123');
});

test('it can be created from new password request', function () {
    $request = NewPasswordRequest::create('/reset-password', 'POST', [
        'token' => 'request-token-456',
        'email' => 'test@example.com',
        'password' => 'secret789',
        'password_confirmation' => 'secret789',
    ]);

    $data = PasswordResetData::fromRequest($request);

    expect($data->token)->toBe('request-token-456')
        ->and($data->email)->toBe('test@example.com')
        ->and($data->password)->toBe('secret789')
        ->and($data->passwordConfirmation)->toBe('secret789');
});

test('it converts to array correctly', function () {
    $data = new PasswordResetData(
        token: 'test-token',
        email: 'user@test.com',
        password: 'testpass',
        passwordConfirmation: 'testpass'
    );

    $array = $data->toArray();

    expect($array)->toBe([
        'token' => 'test-token',
        'email' => 'user@test.com',
        'password' => 'testpass',
        'password_confirmation' => 'testpass',
    ]);
});
