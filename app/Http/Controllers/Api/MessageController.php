<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\User\GetAvatarAction;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class MessageController
{
    /**
     * Store a newly created resource in storage.
     *
     * @throws Throwable
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'content' => 'required|string|max:10000',
            'type' => 'in:text,image,file',
            'metadata' => 'array|nullable',
        ]);

        /** @var User $user */
        $user = Auth::user();

        try {
            /** @var Conversation $conversation */
            $conversation = Conversation::query()
                ->whereHas('participants', function ($q) use ($user): void {
                    $q->where('user_id', $user->id);
                })
                ->findOrFail($request->input('conversation_id'));

            DB::beginTransaction();

            /** @var Message $message */
            $message = Message::query()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'content' => $request->input('content'),
                'type' => $request->input('type', 'text'),
                'metadata' => $request->input('metadata'),
            ]);

            $conversation->update(['last_message_at' => now()]);

            logger()->debug('🔍 MessageController: Despachando evento MessageSent', [
                'message_id' => $message->id,
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'content' => $message->content
            ]);

            MessageSent::dispatch($message);

            DB::commit();

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
                    'avatar_url' => new GetAvatarAction()->handle($messageUser),
                ],
            ], 201);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Conversation not found'], 404);
        } catch (Exception) {
            DB::rollBack();

            return response()->json(['error' => 'Failed to send message'], 500);
        }
    }

    /**
     * Mark messages as read.
     */
    public function markAsRead(Request $request, int $conversationId): JsonResponse
    {
        $request->validate([
            'message_ids' => 'array',
            'message_ids.*' => 'integer|exists:messages,id',
        ]);

        $user = Auth::user();
        if (! $user instanceof User) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            /** @var Conversation $conversation */
            $conversation = Conversation::query()
                ->whereHas('participants', function ($q) use ($user): void {
                    $q->where('user_id', $user->getAttribute('id'));
                })
                ->findOrFail($conversationId);

            $query = Message::query()
                ->where('conversation_id', $conversation->getAttribute('id'))
                ->where('user_id', '!=', $user->getAttribute('id'))
                ->whereNull('read_at');

            if ($request->has('message_ids')) {
                $query->whereIn('id', $request->input('message_ids', []));
            }

            $query->update(['read_at' => now()]);

            $conversation->participants()->updateExistingPivot($user->getAttribute('id'), [
                'last_read_at' => now(),
            ]);

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
        $user = Auth::user();
        if (! $user instanceof User) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            /** @var Message $message */
            $message = Message::query()
                ->where('user_id', $user->getAttribute('id'))
                ->findOrFail($id);
            $message->delete();

            return response()->json(['message' => 'Message deleted successfully']);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Message not found'], 404);
        }
    }
}
