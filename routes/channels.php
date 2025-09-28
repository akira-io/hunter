<?php

declare(strict_types=1);

use App\Actions\User\GetAvatarAction;
use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Authorization for private user channel used by WS events (e.g. ConversationsSnapshot)
Broadcast::channel('user.{id}', function ($user, $id) {

    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    logger()->debug('🔍 Broadcasting auth check', [
        'user_id' => $user->id,
        'conversation_id' => $conversationId
    ]);

    $authorized = Conversation::query()->forUser(user: $user)->where('id', $conversationId)->exists();

    logger()->debug('🔍 Broadcasting auth result', [
        'authorized' => $authorized
    ]);

    return $authorized;
});

Broadcast::channel('presence', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'avatar_url' => new GetAvatarAction()->handle($user),
    ];
});
