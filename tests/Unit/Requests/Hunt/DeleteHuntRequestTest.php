<?php

declare(strict_types=1);

use App\Http\Requests\Hunt\DeleteHuntRequest;
use App\Models\Hunt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->hunt = Hunt::factory()->create(['owner_id' => $this->user->id]);
});

it('authorizes user who owns the hunt', function () {
    $this->actingAs($this->user);

    $response = $this->delete(route('hunts.destroy', $this->hunt));

    $response->assertRedirect();
    expect(Hunt::find($this->hunt->id))->toBeNull();
});

it('does not authorize user who does not own the hunt', function () {
    $otherUser = User::factory()->create();
    $this->actingAs($otherUser);

    $response = $this->delete(route('hunts.destroy', $this->hunt));

    $response->assertForbidden();
    expect(Hunt::find($this->hunt->id))->not->toBeNull();
});

it('has empty validation rules', function () {
    $request = new DeleteHuntRequest;
    $rules = $request->rules();

    expect($rules)->toBeArray()
        ->and($rules)->toBeEmpty();
});

it('can delete hunt using destroy method', function () {
    $request = DeleteHuntRequest::createFrom(
        request()->create("/hunts/{$this->hunt->id}", 'DELETE'),
        new DeleteHuntRequest
    );

    $request->setContainer(app());
    $request->setUserResolver(fn () => $this->user);

    $result = $request->destroy($this->hunt);

    expect($result)->toBeTrue()
        ->and(Hunt::find($this->hunt->id))->toBeNull();
});
