<?php

declare(strict_types=1);

use App\Actions\Chat\DeleteMessageAction;
use App\Actions\Chat\MarkMessagesAsReadAction;
use App\Actions\Chat\SendMessageAction;
use App\Actions\User\GetAvatarAction;
use App\Http\Controllers\Api\MessageController;
use App\Http\Requests\Chat\MarkMessagesAsReadRequest;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

test('markAsRead returns 401 when authenticated user is not User instance', function () {
    // Create a custom authenticatable object that is NOT a User instance
    $customAuth = new class implements Authenticatable
    {
        public function getAuthIdentifierName(): string
        {
            return 'id';
        }

        public function getAuthIdentifier(): mixed
        {
            return 123;
        }

        public function getAuthPasswordName(): string
        {
            return 'password';
        }

        public function getAuthPassword(): string
        {
            return 'password';
        }

        public function getRememberToken(): ?string
        {
            return null;
        }

        public function setRememberToken($value): void {}

        public function getRememberTokenName(): string
        {
            return 'remember_token';
        }
    };

    // Mock Auth facade to return non-User instance
    Auth::shouldReceive('user')->andReturn($customAuth);

    // Create real instances of actions since they are final classes
    $sendMessageAction = app(SendMessageAction::class);
    $markMessagesAsReadAction = app(MarkMessagesAsReadAction::class);
    $deleteMessageAction = app(DeleteMessageAction::class);
    $getAvatarAction = app(GetAvatarAction::class);

    $controller = new MessageController(
        $sendMessageAction,
        $markMessagesAsReadAction,
        $deleteMessageAction,
        $getAvatarAction
    );

    // Create a request with valid data - the controller returns early anyway
    $request = MarkMessagesAsReadRequest::create('/messages/1/mark-as-read', 'PATCH', [
        'message_ids' => [1, 2, 3],
    ]);

    $response = $controller->markAsRead($request, 1);

    expect($response->getStatusCode())->toBe(401)
        ->and($response->getData(true))->toBe(['error' => 'Unauthorized']);
});

test('destroy returns 401 when authenticated user is not User instance', function () {
    // Create a custom authenticatable object that is NOT a User instance
    $customAuth = new class implements Authenticatable
    {
        public function getAuthIdentifierName(): string
        {
            return 'id';
        }

        public function getAuthIdentifier(): mixed
        {
            return 123;
        }

        public function getAuthPasswordName(): string
        {
            return 'password';
        }

        public function getAuthPassword(): string
        {
            return 'password';
        }

        public function getRememberToken(): ?string
        {
            return null;
        }

        public function setRememberToken($value): void {}

        public function getRememberTokenName(): string
        {
            return 'remember_token';
        }
    };

    // Mock Auth facade to return non-User instance
    Auth::shouldReceive('user')->andReturn($customAuth);

    // Create real instances of actions since they are final classes
    $sendMessageAction = app(SendMessageAction::class);
    $markMessagesAsReadAction = app(MarkMessagesAsReadAction::class);
    $deleteMessageAction = app(DeleteMessageAction::class);
    $getAvatarAction = app(GetAvatarAction::class);

    $controller = new MessageController(
        $sendMessageAction,
        $markMessagesAsReadAction,
        $deleteMessageAction,
        $getAvatarAction
    );

    $response = $controller->destroy(1);

    expect($response->getStatusCode())->toBe(401)
        ->and($response->getData(true))->toBe(['error' => 'Unauthorized']);
});
