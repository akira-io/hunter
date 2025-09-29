<?php

declare(strict_types=1);

use App\DataTransferObjects\Hunt\CreateHuntData;
use App\Http\Requests\Hunt\CreateHuntRequest;
use Illuminate\Http\UploadedFile;

test('it can be created with minimal data', function () {
    $data = CreateHuntData::from([
        'content' => 'Test hunt content',
    ]);

    expect($data->content)->toBe('Test hunt content')
        ->and($data->isReported)->toBeFalse()
        ->and($data->isPinned)->toBeFalse()
        ->and($data->isIgnored)->toBeFalse()
        ->and($data->image)->toBeNull();
});

test('it can be created with all data', function () {
    $image = UploadedFile::fake()->image('test.jpg');

    $data = CreateHuntData::from([
        'content' => 'Full hunt content',
        'is_reported' => true,
        'is_pinned' => true,
        'is_ignored' => false,
    ], $image);

    expect($data->content)->toBe('Full hunt content')
        ->and($data->isReported)->toBeTrue()
        ->and($data->isPinned)->toBeTrue()
        ->and($data->isIgnored)->toBeFalse()
        ->and($data->image)->toBe($image);
});

test('it converts to array correctly', function () {
    $data = CreateHuntData::from([
        'content' => 'Test content',
        'is_reported' => true,
        'is_pinned' => false,
        'is_ignored' => true,
    ]);

    $array = $data->toArray();

    expect($array)->toBe([
        'content' => 'Test content',
        'is_reported' => true,
        'is_pinned' => false,
        'is_ignored' => true,
    ]);
});

test('it can be created from create hunt request', function () {
    $request = CreateHuntRequest::create('/hunts', 'POST', [
        'content' => 'Hunt from request',
        'is_reported' => false,
        'is_pinned' => true,
        'is_ignored' => false,
    ]);

    $data = CreateHuntData::fromRequest($request);

    expect($data->content)->toBe('Hunt from request')
        ->and($data->isReported)->toBeFalse()
        ->and($data->isPinned)->toBeTrue()
        ->and($data->isIgnored)->toBeFalse()
        ->and($data->image)->toBeNull();
});

test('it can be created from request with image', function () {
    $image = UploadedFile::fake()->image('test.jpg');

    $request = CreateHuntRequest::create('/hunts', 'POST', [
        'content' => 'Hunt with image from request',
    ]);
    $request->files->set('image', $image);

    $data = CreateHuntData::fromRequest($request);

    expect($data->content)->toBe('Hunt with image from request')
        ->and($data->isReported)->toBeFalse()
        ->and($data->isPinned)->toBeFalse()
        ->and($data->isIgnored)->toBeFalse()
        ->and($data->image)->toBe($image);
});
