<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;

use function Pest\Laravel\from;

beforeEach(function () {
    $this->user = actingAsAuthUser();
});

it('should not  create a hunt if you are a guest ', function () {
    // Simulate a guest use
    auth()->logout();

    $response = from(route('hunts.index'))
        ->post(route('hunts.store'), [
            'title' => 'Hunt Title',
            'description' => 'Hunt Description',
        ]);

    $this->assertGuest();

    $response->assertRedirect(route('login'));

});

it('should create a hunt', function () {
    $response = from(route('hunts.index'))
        ->post(route('hunts.store'), [
            'content' => 'Hunt Description',
        ]);

    $response->assertRedirect(route('hunts.index'));

    $this->assertDatabaseHas('hunts', [
        'content' => 'Hunt Description',
    ]);
});

it('should not create a hunt if the content is empty', function () {
    $response = from(route('hunts.index'))
        ->post(route('hunts.store'), [
            'content' => '',
        ]);

    $response->assertSessionHasErrors(['content']);

    $this->assertDatabaseMissing('hunts', [
        'content' => '',
    ]);
});

it('should not create a hunt if the content is too short less 3 char', function () {
    $response = from(route('hunts.index'))
        ->post(route('hunts.store'), [
            'content' => 'H',
        ]);

    $response->assertSessionHasErrors(['content']);

    $this->assertDatabaseMissing('hunts', [
        'content' => 'H',
    ]);
});

it('should not create a hunt if the content is too long, more than 500 char', function () {
    $response = from(route('hunts.index'))
        ->post(route('hunts.store'), [
            'content' => str_repeat('H', 501),
        ]);

    $response->assertSessionHasErrors(['content']);

    $this->assertDatabaseMissing('hunts', [
        'content' => str_repeat('H', 501),
    ]);
});

it('should  create a hunt if the content contains blank characters at the beginning', function () {
    from(route('hunts.index'))
        ->post(route('hunts.store'), [
            'content' => '  Hunt Title',
        ]);

    $this->assertDatabaseHas('hunts', [
        'content' => 'Hunt Title',
    ]);
});

it('should  create a hunt if the content contains blank characters at the end', function () {
    from(route('hunts.index'))
        ->post(route('hunts.store'), [
            'content' => 'Hunt Title  ',
        ]);

    $this->assertDatabaseHas('hunts', [
        'content' => 'Hunt Title',
    ]);
});

it('should  create a hunt if the content contains blank characters at the beginning and end', function () {
    from(route('hunts.index'))
        ->post(route('hunts.store'), [
            'content' => '  Hunt Title  ',
        ]);

    $this->assertDatabaseHas('hunts', [
        'content' => 'Hunt Title',
    ]);
});

it('should  create a hunt if the content contains blank characters in the middle', function () {
    from(route('hunts.index'))
        ->post(route('hunts.store'), [
            'content' => 'Hunt Title  ',
        ]);

    $this->assertDatabaseHas('hunts', [
        'content' => 'Hunt Title',
    ]);
});

it('should  create a hunt if the content contains blank characters in the middle and at the end', function () {
    from(route('hunts.index'))
        ->post(route('hunts.store'), [
            'content' => 'Hunt Title  ',
        ]);

    $this->assertDatabaseHas('hunts', [
        'content' => 'Hunt Title',
    ]);
});

it('should  create a hunt if the content contains blank characters in the middle and at the beginning', function () {
    from(route('hunts.index'))
        ->post(route('hunts.store'), [
            'content' => '  Hunt Title',
        ]);

    $this->assertDatabaseHas('hunts', [
        'content' => 'Hunt Title',
    ]);
});

it('should  create a hunt if the content contains blank characters in the middle and at the beginning and end', function () {
    from(route('hunts.index'))
        ->post(route('hunts.store'), [
            'content' => '  Hunt Title  ',
        ]);

    $this->assertDatabaseHas('hunts', [
        'content' => 'Hunt Title',
    ]);
});

it('should not create a hunt with blank characters', function () {

    from(route('hunts.index'))
        ->post(route('hunts.store'), [
            'content' => str_repeat(' ', 500),
        ]);

    $this->assertDatabaseMissing('hunts', [
        'content' => str_repeat(' ', 500),
    ]);

});

it('should create a hunt with image', function () {

    Storage::fake('hunts');

    $response = from(route('hunts.index'))
        ->post(route('hunts.store'), [
            'content' => 'Hunt Description',
            'image' => UploadedFile::fake()->image('hunt.jpg'),
        ]);

    $response->assertRedirect(route('hunts.index'));

    $this->assertDatabaseHas('hunts', [
        'content' => 'Hunt Description',
    ]);

    expect($this->user->hunts()->first()->getMedia('hunts'))->toHaveCount(1);

});

it('returns new hunt in session flash with pending status', function () {
    Storage::fake('hunts');

    $response = from(route('hunts.index'))
        ->post(route('hunts.store'), [
            'content' => 'Hunt with image',
            'image' => UploadedFile::fake()->image('test.jpg'),
        ]);

    $response->assertRedirect(route('hunts.index'));
    $response->assertSessionHas('newHunt');

    $newHunt = session('newHunt');
    expect($newHunt)->toHaveKey('id')
        ->and($newHunt)->toHaveKey('content', 'Hunt with image')
        ->and($newHunt)->toHaveKey('image_processing_status', 'pending');
});
