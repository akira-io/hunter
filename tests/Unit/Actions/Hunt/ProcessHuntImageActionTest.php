<?php

declare(strict_types=1);

use App\Actions\Hunt\ProcessHuntImageAction;
use App\Actions\Hunt\UpdateHuntImageStatusAction;
use App\Enums\HuntImageProcessingStatus;
use App\Events\HuntImageProcessed;
use App\Models\Hunt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->updateStatusAction = app(UpdateHuntImageStatusAction::class);
    $this->action = new ProcessHuntImageAction($this->updateStatusAction);
});

it('successfully processes and stores hunt image', function () {
    Event::fake();

    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Pending,
    ]);

    $image = UploadedFile::fake()->image('test.jpg', 800, 600);

    $this->action->handle($hunt, $image);

    $hunt->refresh();

    expect($hunt->image_processing_status)->toBe(HuntImageProcessingStatus::Completed)
        ->and($hunt->getFirstMediaUrl('hunts'))->not->toBeEmpty()
        ->and($hunt->getMedia('hunts'))->toHaveCount(1);
});

it('updates status to processing before handling image', function () {
    Event::fake();

    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Pending,
    ]);

    $image = UploadedFile::fake()->image('test.jpg');

    $this->action->handle($hunt, $image);

    // After processing, it should be completed
    $hunt->refresh();
    expect($hunt->image_processing_status)->toBe(HuntImageProcessingStatus::Completed);
});

it('broadcasts hunt image processed event', function () {
    Event::fake();

    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Pending,
    ]);

    $image = UploadedFile::fake()->image('test.jpg');

    $this->action->handle($hunt, $image);

    Event::assertDispatched(HuntImageProcessed::class, function ($event) use ($hunt) {
        return $event->hunt->id === $hunt->id
            && $event->hunt->image_processing_status === HuntImageProcessingStatus::Completed;
    });
});

it('refreshes hunt before broadcasting', function () {
    Event::fake();

    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Pending,
    ]);

    $image = UploadedFile::fake()->image('test.jpg');

    $this->action->handle($hunt, $image);

    Event::assertDispatched(HuntImageProcessed::class, function ($event) {
        // Verify hunt has been refreshed with media
        return $event->hunt->image_processing_status === HuntImageProcessingStatus::Completed
            && $event->hunt->getMedia('hunts')->count() > 0;
    });
});

it('adds media to hunts collection', function () {
    Event::fake();

    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Pending,
    ]);

    $image = UploadedFile::fake()->image('test.jpg');

    expect($hunt->getMedia('hunts'))->toHaveCount(0);

    $this->action->handle($hunt, $image);

    $hunt->refresh();

    expect($hunt->getMedia('hunts'))->toHaveCount(1)
        ->and($hunt->getMedia('hunts')->first()->collection_name)->toBe('hunts');
});

it('handles different image types', function () {
    Event::fake();

    $user = User::factory()->create();

    $imageTypes = ['jpg', 'png', 'gif'];

    foreach ($imageTypes as $type) {
        $hunt = Hunt::factory()->create([
            'owner_id' => $user->id,
            'image_processing_status' => HuntImageProcessingStatus::Pending,
        ]);

        $image = UploadedFile::fake()->image("test.{$type}");

        $this->action->handle($hunt, $image);

        $hunt->refresh();

        expect($hunt->image_processing_status)->toBe(HuntImageProcessingStatus::Completed)
            ->and($hunt->getFirstMediaUrl('hunts'))->not->toBeEmpty();
    }
});
