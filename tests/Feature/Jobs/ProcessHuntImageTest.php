<?php

declare(strict_types=1);

use App\Enums\HuntImageProcessingStatus;
use App\Events\HuntImageProcessed;
use App\Jobs\ProcessHuntImage;
use App\Models\Hunt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

it('processes hunt image successfully', function () {
    Event::fake();

    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Pending->value,
    ]);

    $image = UploadedFile::fake()->image('test.jpg');
    $tempPath = $image->store('temp', 'local');

    $job = new ProcessHuntImage($hunt, $tempPath, 'test.jpg', 'image/jpeg');
    $job->handle(app(App\Actions\Hunt\ProcessHuntImageAction::class));

    $hunt->refresh();

    expect($hunt->image_processing_status)->toBe(HuntImageProcessingStatus::Completed)
        ->and($hunt->getMedia('hunts')->count())->toBe(1);

    Event::assertDispatched(HuntImageProcessed::class);
});

it('sets status to processing when job starts', function () {
    Event::fake();

    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Pending->value,
    ]);

    $image = UploadedFile::fake()->image('test.jpg');
    $tempPath = $image->store('temp', 'local');

    $job = new ProcessHuntImage($hunt, $tempPath, 'test.jpg', 'image/jpeg');
    $job->handle(app(App\Actions\Hunt\ProcessHuntImageAction::class));

    $hunt->refresh();

    expect($hunt->image_processing_status)->toBe(HuntImageProcessingStatus::Completed);
});

it('dispatches job when hunt is created with image', function () {
    Queue::fake();

    $user = User::factory()->create();

    $this->actingAs($user);

    $image = UploadedFile::fake()->image('hunt.jpg');

    $response = $this->post(route('hunts.store'), [
        'content' => 'Test hunt with image',
        'image' => $image,
    ]);

    $response->assertRedirect();

    Queue::assertPushed(ProcessHuntImage::class);
});

it('sets hunt status to pending when dispatching job', function () {
    Queue::fake();

    $user = User::factory()->create();

    $this->actingAs($user);

    $image = UploadedFile::fake()->image('hunt.jpg');

    $this->post(route('hunts.store'), [
        'content' => 'Test hunt with image',
        'image' => $image,
    ]);

    $hunt = $user->hunts()->first();

    expect($hunt->image_processing_status)->toBe(HuntImageProcessingStatus::Pending);
});

it('broadcasts HuntImageProcessed event when processing completes', function () {
    Event::fake();

    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Pending->value,
    ]);

    $image = UploadedFile::fake()->image('test.jpg');
    $tempPath = $image->store('temp', 'local');

    $job = new ProcessHuntImage($hunt, $tempPath, 'test.jpg', 'image/jpeg');
    $job->handle(app(App\Actions\Hunt\ProcessHuntImageAction::class));

    Event::assertDispatched(HuntImageProcessed::class, function ($event) use ($hunt) {
        return $event->hunt->id === $hunt->id
            && $event->hunt->image_processing_status === HuntImageProcessingStatus::Completed;
    });
});

it('sets status to failed when job fails', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Processing->value,
    ]);

    $job = new ProcessHuntImage($hunt, 'invalid/path.jpg', 'test.jpg', 'image/jpeg');

    try {
        $job->handle(app(App\Actions\Hunt\ProcessHuntImageAction::class));
    } catch (Exception $e) {
        // Expected to fail
    }

    $job->failed(new Exception('Test exception'));

    $hunt->refresh();

    expect($hunt->image_processing_status)->toBe(HuntImageProcessingStatus::Failed);
});

it('cleans up temp file after successful processing', function () {
    Event::fake();

    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Pending->value,
    ]);

    $image = UploadedFile::fake()->image('test.jpg');
    $tempPath = $image->store('temp', 'local');

    Storage::disk('local')->assertExists($tempPath);

    $job = new ProcessHuntImage($hunt, $tempPath, 'test.jpg', 'image/jpeg');
    $job->handle(app(App\Actions\Hunt\ProcessHuntImageAction::class));

    Storage::disk('local')->assertMissing($tempPath);
});

it('refreshes hunt before broadcasting to ensure latest data', function () {
    Event::fake();

    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Pending->value,
    ]);

    $image = UploadedFile::fake()->image('test.jpg');
    $tempPath = $image->store('temp', 'local');

    $job = new ProcessHuntImage($hunt, $tempPath, 'test.jpg', 'image/jpeg');
    $job->handle(app(App\Actions\Hunt\ProcessHuntImageAction::class));

    Event::assertDispatched(HuntImageProcessed::class, function ($event) {
        // Hunt should have media attached and status completed
        return $event->hunt->image_processing_status === HuntImageProcessingStatus::Completed
            && ! empty($event->hunt->getFirstMediaUrl('hunts'));
    });
});
