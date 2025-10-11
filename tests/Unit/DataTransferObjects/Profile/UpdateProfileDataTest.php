<?php

declare(strict_types=1);

use App\DataTransferObjects\Profile\UpdateProfileData;
use App\Http\Requests\Settings\ProfileUpdateRequest;

test('it can be created from array data', function () {
    $data = UpdateProfileData::fromArray([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'bio' => 'Software developer',
        'location' => 'New York',
    ]);

    expect($data->name)->toBe('John Doe')
        ->and($data->email)->toBe('john@example.com')
        ->and($data->bio)->toBe('Software developer')
        ->and($data->location)->toBe('New York');
});

test('it can be created from array data with null values', function () {
    $data = UpdateProfileData::fromArray([
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'bio' => null,
        'location' => null,
    ]);

    expect($data->name)->toBe('Jane Smith')
        ->and($data->email)->toBe('jane@example.com')
        ->and($data->bio)->toBeNull()
        ->and($data->location)->toBeNull();
});

test('it can be created from profile update request', function () {
    $request = ProfileUpdateRequest::create('/profile', 'PATCH', [
        'name' => 'Alice Johnson',
        'email' => 'alice@example.com',
        'bio' => 'Frontend developer',
        'location' => 'San Francisco',
    ]);

    $data = UpdateProfileData::fromRequest($request);

    expect($data->name)->toBe('Alice Johnson')
        ->and($data->email)->toBe('alice@example.com')
        ->and($data->bio)->toBe('Frontend developer')
        ->and($data->location)->toBe('San Francisco');
});

test('it can be created from request with missing optional fields', function () {
    $request = ProfileUpdateRequest::create('/profile', 'PATCH', [
        'name' => 'Bob Wilson',
        'email' => 'bob@example.com',
    ]);

    $data = UpdateProfileData::fromRequest($request);

    expect($data->name)->toBe('Bob Wilson')
        ->and($data->email)->toBe('bob@example.com')
        ->and($data->bio)->toBeNull()
        ->and($data->location)->toBeNull();
});

test('it converts to array correctly', function () {
    $data = new UpdateProfileData(
        name: 'Test User',
        email: 'test@example.com',
        bio: 'Test bio',
        location: 'Test location'
    );

    $array = $data->toArray();

    expect($array)->toBe([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'bio' => 'Test bio',
        'location' => 'Test location',
    ]);
});

test('it filters out null values in toArray', function () {
    $data = new UpdateProfileData(
        name: 'Test User',
        email: 'test@example.com',
        bio: null,
        location: null
    );

    $array = $data->toArray();

    expect($array)->toBe([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
});
