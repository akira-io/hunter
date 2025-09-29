<?php

declare(strict_types=1);

use App\Actions\Hunt\CreateHuntAction;
use App\DataTransferObjects\Hunt\CreateHuntData;
use App\Models\Hunt;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('it can create a hunt for a user', function () {
    $user = User::factory()->create();
    $action = new CreateHuntAction();
    $huntData = CreateHuntData::from([
        'content' => 'This is a test hunt',
        'is_reported' => false,
        'is_pinned' => false,
        'is_ignored' => false,
    ]);

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
    $image = UploadedFile::fake()->image('test.jpg');

    $huntData = CreateHuntData::from([
        'content' => 'Hunt with image',
    ], $image);

    $hunt = $action->handle($user, $huntData);

    expect($hunt)->toBeInstanceOf(Hunt::class)
        ->and($hunt->content)->toBe('Hunt with image')
        ->and($hunt->owner_id)->toBe($user->id);

    expect($hunt->getMedia('hunts'))->toHaveCount(1);
});
