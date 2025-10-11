<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Chat\DeleteMessageAction;
use App\Actions\Chat\MarkMessagesAsReadAction;
use App\Actions\Chat\SendMessageAction;
use App\Actions\User\GetAvatarAction;
use App\Http\Requests\Chat\MarkMessagesAsReadRequest;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;

final readonly class MessageController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private SendMessageAction $sendMessageAction,
        private MarkMessagesAsReadAction $markMessagesAsReadAction,
        private DeleteMessageAction $deleteMessageAction,
        private GetAvatarAction $getAvatarAction
    ) {}

    /**
     * Store a newly created resource in storage.
     *
     * @throws Throwable
     */
    public function store(SendMessageRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            /** @var \App\Models\Message $message */
            $message = $this->sendMessageAction->handle(
                $user,
                $request->getConversationId(),
                $request->getMessageContent(),
                $request->getType(),
                $request->getMetadata()
            );

            /** @var User $messageUser */
            $messageUser = $message->user;

            return response()->json([
                'id' => $message->id,
                'content' => $message->content,
                'type' => $message->type,
                'metadata' => $message->metadata,
                'created_at' => $message->created_at,
                'user' => [
                    'id' => $messageUser->id,
                    'name' => $messageUser->name,
                    'avatar_url' => $this->getAvatarAction->handle($messageUser),
                ],
            ], 201);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Conversation not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark messages as read.
     */
    public function markAsRead(MarkMessagesAsReadRequest $request, int $conversationId): JsonResponse
    {
        /** @var mixed $user */
        $user = Auth::user();
        if (! $user instanceof User) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            /** @var int $updatedCount */
            $updatedCount = $this->markMessagesAsReadAction->handle(
                $user,
                $conversationId,
                $request->getMessageIds()
            );

            return response()->json(['message' => 'Messages marked as read']);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var mixed $user */
        $user = Auth::user();
        if (! $user instanceof User) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $this->deleteMessageAction->handle($user, $id);

            return response()->json(['message' => 'Message deleted successfully']);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Message not found'], 404);
        }
    }
}
