<?php

declare(strict_types=1);

use App\Http\Requests\Hunt\CreateHuntRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Storage::fake('public');
});

it('authorizes all requests', function () {
    $request = new CreateHuntRequest;

    expect($request->authorize())->toBeTrue();
});

it('has correct validation rules', function () {
    $request = new CreateHuntRequest;
    $rules = $request->rules();

    expect($rules)->toHaveKey('content')
        ->and($rules)->toHaveKey('is_reported')
        ->and($rules)->toHaveKey('is_pinned')
        ->and($rules)->toHaveKey('is_ignored')
        ->and($rules)->toHaveKey('image');
});

it('can store a hunt without image using store method', function () {
    $this->actingAs($this->user);

    $request = $this->post(route('hunts.store'), [
        'content' => 'This is a test hunt via store method',
    ]);

    $request->assertRedirect();

    expect($this->user->hunts()->where('content', 'This is a test hunt via store method')->exists())->toBeTrue();
});

it('can store a hunt with image using store method', function () {
    $this->actingAs($this->user);

    Storage::fake('hunts');
    $image = UploadedFile::fake()->image('hunt.jpg');

    $request = $this->post(route('hunts.store'), [
        'content' => 'This is a test hunt with image',
        'image' => $image,
    ]);

    $request->assertRedirect();

    $hunt = $this->user->hunts()->where('content', 'This is a test hunt with image')->first();
    expect($hunt)->not->toBeNull()
        ->and($hunt->getMedia('hunts')->count())->toBe(1);
});
