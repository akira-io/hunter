<?php

declare(strict_types=1);

use App\Actions\Hunt\CreateHuntAction;
use App\Models\Hunt;
use App\Models\User;

test('it can create a hunt for a user', function () {
    $user = User::factory()->create();
    $action = new CreateHuntAction();
    $huntData = [
        'content' => 'This is a test hunt',
        'is_reported' => false,
        'is_pinned' => false,
        'is_ignored' => false,
    ];

    $hunt = $action->handle($user, $huntData);

    expect($hunt)->toBeInstanceOf(Hunt::class)
        ->and($hunt->content)->toBe('This is a test hunt')
        ->and($hunt->owner_id)->toBe($user->id)
        ->and($hunt->is_reported)->toBe(false);

    $this->assertDatabaseHas('hunts', [
        'id' => $hunt->id,
        'content' => 'This is a test hunt',
        'owner_id' => $user->id,
    ]);
});

test('it can create a hunt with image', function () {
    $user = User::factory()->create();
    $action = new CreateHuntAction();
    $huntData = [
        'content' => 'Hunt with image',
    ];

    $image = Illuminate\Http\UploadedFile::fake()->image('test.jpg');

    $hunt = $action->handle($user, $huntData, $image);

    expect($hunt)->toBeInstanceOf(Hunt::class)
        ->and($hunt->content)->toBe('Hunt with image')
        ->and($hunt->owner_id)->toBe($user->id);

    expect($hunt->getMedia('hunts'))->toHaveCount(1);
});
