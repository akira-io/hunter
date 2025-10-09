<?php

declare(strict_types=1);

use App\Actions\Hunt\UpdateHuntImageStatusAction;
use App\Enums\HuntImageProcessingStatus;
use App\Models\Hunt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can update hunt image status to pending', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Completed,
    ]);

    $action = new UpdateHuntImageStatusAction();
    $action->handle($hunt, HuntImageProcessingStatus::Pending);

    $hunt->refresh();

    expect($hunt->image_processing_status)->toBe(HuntImageProcessingStatus::Pending);
});

it('can update hunt image status to processing', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Pending,
    ]);

    $action = new UpdateHuntImageStatusAction();
    $action->handle($hunt, HuntImageProcessingStatus::Processing);

    $hunt->refresh();

    expect($hunt->image_processing_status)->toBe(HuntImageProcessingStatus::Processing);
});

it('can update hunt image status to completed', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Processing,
    ]);

    $action = new UpdateHuntImageStatusAction();
    $action->handle($hunt, HuntImageProcessingStatus::Completed);

    $hunt->refresh();

    expect($hunt->image_processing_status)->toBe(HuntImageProcessingStatus::Completed);
});

it('can update hunt image status to failed', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Processing,
    ]);

    $action = new UpdateHuntImageStatusAction();
    $action->handle($hunt, HuntImageProcessingStatus::Failed);

    $hunt->refresh();

    expect($hunt->image_processing_status)->toBe(HuntImageProcessingStatus::Failed);
});

it('persists status change to database', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'image_processing_status' => HuntImageProcessingStatus::Pending,
    ]);

    $action = new UpdateHuntImageStatusAction();
    $action->handle($hunt, HuntImageProcessingStatus::Completed);

    $this->assertDatabaseHas('hunts', [
        'id' => $hunt->id,
        'image_processing_status' => HuntImageProcessingStatus::Completed->value,
    ]);
});
