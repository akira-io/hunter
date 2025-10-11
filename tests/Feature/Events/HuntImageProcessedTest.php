<?php

declare(strict_types=1);

use App\Enums\HuntImageProcessingStatus;
use App\Events\HuntImageProcessed;
use App\Models\Hunt;
use App\Models\User;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('broadcasts on public hunts channel', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Completed,
    ]);

    $event = new HuntImageProcessed($hunt);
    $channels = $event->broadcastOn();

    expect($channels)->toHaveCount(1)
        ->and($channels[0]->name)->toBe('hunts');
});

it('broadcasts with event name hunt.image.processed', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Completed,
    ]);

    $event = new HuntImageProcessed($hunt);

    expect($event->broadcastAs())->toBe('hunt.image.processed');
});

it('broadcasts hunt data via HuntResource', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'content' => 'Test hunt content',
        'image_processing_status' => HuntImageProcessingStatus::Completed,
    ]);

    $event = new HuntImageProcessed($hunt);
    $data = $event->broadcastWith();

    expect($data)->toHaveKey('hunt')
        ->and($data['hunt'])->toHaveKey('id', $hunt->id)
        ->and($data['hunt'])->toHaveKey('content', 'Test hunt content')
        ->and($data['hunt'])->toHaveKey('image_processing_status', 'completed');
});

it('includes image_url in broadcast data', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Completed,
    ]);

    $event = new HuntImageProcessed($hunt);
    $data = $event->broadcastWith();

    expect($data['hunt'])->toHaveKey('image_url');
});

it('includes image_processing_status in broadcast data', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Completed,
    ]);

    $event = new HuntImageProcessed($hunt);
    $data = $event->broadcastWith();

    expect($data['hunt'])->toHaveKey('image_processing_status')
        ->and($data['hunt']['image_processing_status'])->toBe('completed');
});

it('is dispatched when hunt image processing completes', function () {
    Event::fake([HuntImageProcessed::class]);

    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Completed,
    ]);

    broadcast(new HuntImageProcessed($hunt));

    Event::assertDispatched(HuntImageProcessed::class, function ($event) use ($hunt) {
        return $event->hunt->id === $hunt->id;
    });
});

it('implements ShouldBroadcast interface', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

    $event = new HuntImageProcessed($hunt);

    expect($event)->toBeInstanceOf(ShouldBroadcast::class);
});

it('broadcasts with different processing statuses', function () {
    $user = User::factory()->create();

    $statuses = [
        HuntImageProcessingStatus::Pending,
        HuntImageProcessingStatus::Processing,
        HuntImageProcessingStatus::Completed,
        HuntImageProcessingStatus::Failed,
    ];

    foreach ($statuses as $status) {
        $hunt = Hunt::factory()->create([
            'owner_id' => $user->id,
            'image_processing_status' => $status,
        ]);

        $event = new HuntImageProcessed($hunt);
        $data = $event->broadcastWith();

        expect($data['hunt']['image_processing_status'])->toBe($status->value);
    }
});

it('includes owner information in broadcast data', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'user_name' => 'johndoe',
    ]);

    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Completed,
    ]);

    $event = new HuntImageProcessed($hunt);
    $data = $event->broadcastWith();

    expect($data['hunt'])->toHaveKey('owner')
        ->and($data['hunt']['owner'])->toHaveKey('name')
        ->and($data['hunt']['owner'])->toHaveKey('user_name');
});
