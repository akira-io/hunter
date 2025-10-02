<?php

declare(strict_types=1);

use App\Http\Requests\Hunt\CreateHuntRequest;
use App\Models\Hunt;
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

it('validates content is required for hunt creation', function () {
    $this->actingAs($this->user);

    $response = $this->post(route('hunts.store'), [
        'content' => '',
    ]);

    $response->assertSessionHasErrors(['content']);
});

it('validates image must be an image file', function () {
    $this->actingAs($this->user);

    $file = UploadedFile::fake()->create('document.pdf', 100);

    $response = $this->post(route('hunts.store'), [
        'content' => 'Hunt with invalid file',
        'image' => $file,
    ]);

    $response->assertSessionHasErrors(['image']);
});

it('accepts boolean flags for hunt creation', function () {
    $this->actingAs($this->user);

    $response = $this->post(route('hunts.store'), [
        'content' => 'Hunt with flags',
        'is_pinned' => true,
        'is_reported' => false,
    ]);

    $response->assertRedirect();

    $hunt = $this->user->hunts()->where('content', 'Hunt with flags')->first();
    expect($hunt)->not->toBeNull()
        ->and($hunt->is_pinned)->toBeTrue()
        ->and($hunt->is_reported)->toBeFalse();
});

it('store method directly creates hunt without image', function () {
    // Test the store() method directly on CreateHuntRequest
    $this->actingAs($this->user);

    // Create a real HTTP request through the application
    $response = $this->call('POST', '/hunts', [
        'content' => 'Direct store via POST',
        'is_pinned' => true,
    ]);

    $response->assertRedirect();

    // Verify the hunt was created (reload user to get fresh relationship)
    $this->user->refresh();
    $hunt = $this->user->hunts()->where('content', 'Direct store via POST')->first();

    expect($hunt)->not->toBeNull()
        ->and($hunt->content)->toBe('Direct store via POST')
        ->and($hunt->is_pinned)->toBeTrue();
});

it('store method directly creates hunt with image', function () {
    // Test the store() method with image upload through real request
    $this->actingAs($this->user);

    Storage::fake('public');
    $image = UploadedFile::fake()->image('direct-test.jpg');

    $this->call('POST', '/hunts', [
        'content' => 'Hunt with image direct test',
    ], [], [
        'image' => $image,
    ]);

    $hunt = $this->user->hunts()->where('content', 'Hunt with image direct test')->first();

    expect($hunt)->not->toBeNull()
        ->and($hunt->content)->toBe('Hunt with image direct test')
        ->and($hunt->getMedia('hunts'))->toHaveCount(1);
});
