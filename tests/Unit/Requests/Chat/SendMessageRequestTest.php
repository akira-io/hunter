<?php

declare(strict_types=1);

use App\Http\Requests\Chat\SendMessageRequest;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->conversation = Conversation::factory()->create();
    $this->conversation->participants()->attach($this->user);
});

it('authorizes all requests', function () {
    $request = new SendMessageRequest;

    expect($request->authorize())->toBeTrue();
});

it('has correct validation rules', function () {
    $request = new SendMessageRequest;
    $rules = $request->rules();

    expect($rules)->toHaveKey('conversation_id')
        ->and($rules)->toHaveKey('content')
        ->and($rules)->toHaveKey('type')
        ->and($rules)->toHaveKey('metadata');
});

it('returns correct conversation id from validated data', function () {
    $request = SendMessageRequest::createFrom(
        request()->create('/api/messages', 'POST', [
            'conversation_id' => $this->conversation->id,
            'content' => 'Test message',
        ]),
        new SendMessageRequest
    );

    $request->setContainer(app());
    $request->setUserResolver(fn () => $this->user);
    $request->validateResolved();

    expect($request->getConversationId())->toBe($this->conversation->id);
});

it('returns correct message content from validated data', function () {
    $request = SendMessageRequest::createFrom(
        request()->create('/api/messages', 'POST', [
            'conversation_id' => $this->conversation->id,
            'content' => 'Test message content',
        ]),
        new SendMessageRequest
    );

    $request->setContainer(app());
    $request->setUserResolver(fn () => $this->user);
    $request->validateResolved();

    expect($request->getMessageContent())->toBe('Test message content');
});

it('returns correct type from validated data', function () {
    $request = SendMessageRequest::createFrom(
        request()->create('/api/messages', 'POST', [
            'conversation_id' => $this->conversation->id,
            'content' => 'Test message',
            'type' => 'image',
        ]),
        new SendMessageRequest
    );

    $request->setContainer(app());
    $request->setUserResolver(fn () => $this->user);
    $request->validateResolved();

    expect($request->getType())->toBe('image');
});

it('returns default type text when not provided', function () {
    $request = SendMessageRequest::createFrom(
        request()->create('/api/messages', 'POST', [
            'conversation_id' => $this->conversation->id,
            'content' => 'Test message',
        ]),
        new SendMessageRequest
    );

    $request->setContainer(app());
    $request->setUserResolver(fn () => $this->user);
    $request->validateResolved();

    expect($request->getType())->toBe('text');
});

it('returns metadata from validated data', function () {
    $metadata = ['key' => 'value', 'foo' => 'bar'];

    $request = SendMessageRequest::createFrom(
        request()->create('/api/messages', 'POST', [
            'conversation_id' => $this->conversation->id,
            'content' => 'Test message',
            'metadata' => $metadata,
        ]),
        new SendMessageRequest
    );

    $request->setContainer(app());
    $request->setUserResolver(fn () => $this->user);
    $request->validateResolved();

    expect($request->getMetadata())->toBe($metadata);
});

it('returns null metadata when not provided', function () {
    $request = SendMessageRequest::createFrom(
        request()->create('/api/messages', 'POST', [
            'conversation_id' => $this->conversation->id,
            'content' => 'Test message',
        ]),
        new SendMessageRequest
    );

    $request->setContainer(app());
    $request->setUserResolver(fn () => $this->user);
    $request->validateResolved();

    expect($request->getMetadata())->toBeNull();
});

it('has custom error messages', function () {
    $request = new SendMessageRequest;
    $messages = $request->messages();

    expect($messages)->toHaveKey('conversation_id.required')
        ->and($messages)->toHaveKey('conversation_id.exists')
        ->and($messages)->toHaveKey('content.required')
        ->and($messages)->toHaveKey('content.max')
        ->and($messages)->toHaveKey('type.in')
        ->and($messages)->toHaveKey('metadata.array');
});
