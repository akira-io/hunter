<?php

declare(strict_types=1);

use App\Actions\Chat\CreateConversationAction;
use App\Actions\Chat\DeleteConversationAction;
use App\Actions\Chat\GetConversationMessagesAction;
use App\Actions\Chat\GetConversationsAction;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Requests\Chat\CreateConversationRequest;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

test('index returns 401 when authenticated user is not User instance', function () {
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
    $getConversationsAction = app(GetConversationsAction::class);
    $createConversationAction = app(CreateConversationAction::class);
    $getConversationMessagesAction = app(GetConversationMessagesAction::class);
    $deleteConversationAction = app(DeleteConversationAction::class);

    $controller = new ConversationController(
        $getConversationsAction,
        $createConversationAction,
        $getConversationMessagesAction,
        $deleteConversationAction
    );

    $response = $controller->index();

    expect($response->getStatusCode())->toBe(401)
        ->and($response->getData(true))->toBe(['error' => 'Unauthorized']);
});

test('store returns 401 when authenticated user is not User instance', function () {
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
    $getConversationsAction = app(GetConversationsAction::class);
    $createConversationAction = app(CreateConversationAction::class);
    $getConversationMessagesAction = app(GetConversationMessagesAction::class);
    $deleteConversationAction = app(DeleteConversationAction::class);

    $controller = new ConversationController(
        $getConversationsAction,
        $createConversationAction,
        $getConversationMessagesAction,
        $deleteConversationAction
    );

    // Create a request with valid data - the controller returns early anyway
    $request = CreateConversationRequest::create('/conversations', 'POST', [
        'type' => 'direct',
        'participants' => [1],
    ]);

    $response = $controller->store($request);

    expect($response->getStatusCode())->toBe(401)
        ->and($response->getData(true))->toBe(['error' => 'Unauthorized']);
});

test('show returns 401 when authenticated user is not User instance', function () {
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
    $getConversationsAction = app(GetConversationsAction::class);
    $createConversationAction = app(CreateConversationAction::class);
    $getConversationMessagesAction = app(GetConversationMessagesAction::class);
    $deleteConversationAction = app(DeleteConversationAction::class);

    $controller = new ConversationController(
        $getConversationsAction,
        $createConversationAction,
        $getConversationMessagesAction,
        $deleteConversationAction
    );

    $response = $controller->show(1);

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

        public function getAuthIdentifier(): mixed
        {
            return 123;
        }
    };

    // Mock Auth facade to return non-User instance
    Auth::shouldReceive('user')->andReturn($customAuth);

    // Create real instances of actions since they are final classes
    $getConversationsAction = app(GetConversationsAction::class);
    $createConversationAction = app(CreateConversationAction::class);
    $getConversationMessagesAction = app(GetConversationMessagesAction::class);
    $deleteConversationAction = app(DeleteConversationAction::class);

    $controller = new ConversationController(
        $getConversationsAction,
        $createConversationAction,
        $getConversationMessagesAction,
        $deleteConversationAction
    );

    $response = $controller->destroy(1);

    expect($response->getStatusCode())->toBe(401)
        ->and($response->getData(true))->toBe(['error' => 'Unauthorized']);
});
