<?php

declare(strict_types=1);

use App\Actions\User\GetAvatarAction;
use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function (App\Models\User $user, int $id) {
    return $user->id === $id;
});

// Authorization for private user channel used by WS events (e.g. ConversationsSnapshot)
Broadcast::channel('user.{id}', function (App\Models\User $user, int $id) {

    return $user->id === $id;
});

Broadcast::channel('conversation.{conversationId}', function (App\Models\User $user, $conversationId) {
    return Conversation::forUser($user)
        ->where('id', $conversationId)->exists();
});

Broadcast::channel('presence', function (App\Models\User $user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'avatar_url' => new GetAvatarAction()->handle($user),
    ];
});
