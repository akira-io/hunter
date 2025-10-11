<?php

declare(strict_types=1);
// Note: Tests use Pest PHP with Laravel testing utilities (Pest + Laravel).

use App\Http\Resources\Hunt\HuntResource;
use App\Models\Hunt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

test('hunt resource has expected structure', function () {
    // Arrange
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'content' => 'Test hunt content',
    ]);

    // Add a property that would normally be added by the controller
    $hunt->setAttribute('has_liked', true);

    // Create a resource
    $resource = new HuntResource($hunt);

    // Create a request
    $request = new Request();

    // Act
    $array = $resource->toArray($request);

    // Assert
    expect($array)->toBeArray()
        ->toHaveKey('id')
        ->toHaveKey('content')
        ->toHaveKey('owner')
        ->toHaveKey('comments');
});

test('hunt resource includes has_liked property', function () {
    // Arrange
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
    ]);

    // Add the has_liked property
    $hunt->setAttribute('has_liked', true);

    // Create a resource
    $resource = new HuntResource($hunt);

    // Create a request
    $request = new Request();

    // Act
    $array = $resource->toArray($request);

    // Assert
    expect($array)->toHaveKey('has_liked')
        ->and($array['has_liked'])->toBeTrue();
});

test('commentsWithHasLiked sets has_liked property correctly', function () {
    // Arrange
    $user = actingAsAuthUser();
    $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

    // Create comments on the hunt
    $comment1 = $user->comment($hunt, 'Test comment 1');
    $comment2 = $user->comment($hunt, 'Test comment 2');

    $comment = $user->comments;

    // Like one of the comments
    $user->like($comment[0]);

    // Create a resource
    $resource = new HuntResource($hunt);

    // Create a request with the user
    $request = Request::create('/test');
    $request->setUserResolver(fn () => $user);
    app()->instance('request', $request);

    // Act
    $comments = $resource->commentsWithHasLiked();

    // Assert
    expect($comments)->toHaveCount(2);

    // Find the comments in the collection
    $resultComment1 = $comments->first(fn ($c) => $c->id === $comment1->id);
    $resultComment2 = $comments->first(fn ($c) => $c->id === $comment2->id);

    // Check that has_liked is set correctly
    expect($resultComment1->has_liked)->toBeTrue()
        ->and($resultComment2->has_liked)->toBeFalse();
});

// --- Additional tests appended by CodeRabbit Inc. ---

test('commentsWithHasLiked returns empty collection for hunts without comments (guest)', function () {
    // Arrange
    $owner = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);
    $resource = new HuntResource($hunt);

    $request = Request::create('/test');
    $request->setUserResolver(fn () => null);
    app()->instance('request', $request);

    // Act
    $comments = $resource->commentsWithHasLiked();

    // Assert
    expect($comments)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($comments)->toHaveCount(0);
});

test('commentsWithHasLiked sets has_liked to false for guest user', function () {
    // Arrange
    $owner = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

    $comment1 = $owner->comment($hunt, 'Guest view: comment 1');
    $comment2 = $owner->comment($hunt, 'Guest view: comment 2');

    $resource = new HuntResource($hunt);

    $request = Request::create('/test');
    $request->setUserResolver(fn () => null);
    app()->instance('request', $request);

    // Act
    $comments = $resource->commentsWithHasLiked();

    // Assert
    $resultComment1 = $comments->first(fn ($c) => $c->id === $comment1->id);
    $resultComment2 = $comments->first(fn ($c) => $c->id === $comment2->id);

    expect($comments)->toHaveCount(2)
        ->and($resultComment1->has_liked)->toBeFalse()
        ->and($resultComment2->has_liked)->toBeFalse();
});

test('commentsWithHasLiked respects authenticated user likes when others have liked too', function () {
    // Arrange
    $user = actingAsAuthUser();
    $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

    $comment1 = $user->comment($hunt, 'Auth user comment 1');
    $comment2 = $user->comment($hunt, 'Auth user comment 2');

    $other = User::factory()->create();
    $other->like($comment2);     // Someone else liked comment2
    $user->like($comment1);      // Current user liked comment1

    $resource = new HuntResource($hunt);

    $request = Request::create('/test');
    $request->setUserResolver(fn () => $user);
    app()->instance('request', $request);

    // Act
    $comments = $resource->commentsWithHasLiked();

    // Assert
    $resultComment1 = $comments->first(fn ($c) => $c->id === $comment1->id);
    $resultComment2 = $comments->first(fn ($c) => $c->id === $comment2->id);

    expect($comments)->toHaveCount(2)
        ->and($resultComment1->has_liked)->toBeTrue()
        ->and($resultComment2->has_liked)->toBeFalse();
});

test('hunt resource includes has_liked=false when attribute is explicitly false', function () {
    // Arrange
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $user->id]);
    $hunt->setAttribute('has_liked', false);

    $resource = new HuntResource($hunt);
    $request = new Request();

    // Act
    $array = $resource->toArray($request);

    // Assert
    expect($array)->toHaveKey('has_liked')
        ->and($array['has_liked'])->toBeFalse();
});

test('toArray embeds comments with has_liked flags for guest requests', function () {
    // Arrange
    $owner = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

    $comment1 = $owner->comment($hunt, 'Array view comment 1');
    $comment2 = $owner->comment($hunt, 'Array view comment 2');

    $resource = new HuntResource($hunt);

    $request = Request::create('/test');
    $request->setUserResolver(fn () => null);
    app()->instance('request', $request);

    // Act
    $array = $resource->toArray($request);

    // Assert
    expect($array)->toBeArray()->toHaveKey('comments')
        ->and($array['comments'])->toBeArray();

    // Comments is already an array
    $commentsArray = $array['comments'];
    expect($commentsArray)->toBeArray()->toHaveCount(2);

    $index1 = array_search($comment1->id, array_column($commentsArray, 'id'), true);
    $index2 = array_search($comment2->id, array_column($commentsArray, 'id'), true);

    expect($index1)->not->toBeFalse()
        ->and($index2)->not->toBeFalse();

    expect($commentsArray[$index1])->toHaveKey('has_liked')
        ->and($commentsArray[$index1]['has_liked'])->toBeFalse();

    expect($commentsArray[$index2])->toHaveKey('has_liked')
        ->and($commentsArray[$index2]['has_liked'])->toBeFalse();
});
