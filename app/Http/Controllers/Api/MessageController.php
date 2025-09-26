<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\User\GetAvatarAction;
use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class MessageController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'content' => 'required|string|max:10000',
            'type' => 'in:text,image,file',
            'metadata' => 'array|nullable',
        ]);

        $user = Auth::user();

        try {
            $conversation = Conversation::forUser($user)->findOrFail($request->conversation_id);

            DB::beginTransaction();

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'content' => $request->content,
                'type' => $request->type ?? 'text',
                'metadata' => $request->metadata,
            ]);

            $conversation->update(['last_message_at' => now()]);

            $message->load('user');

            MessageSent::dispatch($message);

            DB::commit();

            return response()->json([
                'id' => $message->id,
                'content' => $message->content,
                'type' => $message->type,
                'metadata' => $message->metadata,
                'created_at' => $message->created_at,
                'user' => [
                    'id' => $message->user->id,
                    'name' => $message->user->name,
                    'avatar_url' => new GetAvatarAction()->handle($message->user),
                ],
            ], 201);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Conversation not found'], 404);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Failed to send message'], 500);
        }
    }

    public function markAsRead(Request $request, int $conversationId): JsonResponse
    {
        $request->validate([
            'message_ids' => 'array',
            'message_ids.*' => 'integer|exists:messages,id',
        ]);

        $user = Auth::user();

        try {
            $conversation = Conversation::forUser($user)->findOrFail($conversationId);

            $query = Message::where('conversation_id', $conversation->id)
                ->where('user_id', '!=', $user->id)
                ->whereNull('read_at');

            if ($request->has('message_ids')) {
                $query->whereIn('id', $request->message_ids);
            }

            $query->update(['read_at' => now()]);

            $participant = $conversation->participants()->where('user_id', $user->id)->first();
            if ($participant) {
                $participant->pivot->updateLastRead();
            }

            return response()->json(['message' => 'Messages marked as read']);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();

        try {
            $message = Message::where('user_id', $user->id)->findOrFail($id);
            $message->delete();

            return response()->json(['message' => 'Message deleted successfully']);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Message not found'], 404);
        }
    }
}
