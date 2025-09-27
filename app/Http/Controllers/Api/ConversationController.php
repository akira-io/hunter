<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\User\GetAvatarAction;
use App\Events\ConversationCreated;
use App\Models\Conversation;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ConversationController
{
    /**
     * Get the conversations of the authenticated user.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();

        $conversations = $user->conversations()
            ->with(['participants', 'messages' => function ($query): void {
                $query->latest()->limit(1)->with('user');
            }])
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function (Conversation $conversation) use ($user): array {
                $lastMessage = $conversation->messages->first();
                $otherParticipants = $conversation->participants->where('id', '!=', $user->id);

                $otherParticipant = $otherParticipants->first();

                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title ?: $otherParticipants->pluck('name')->join(', '),
                    'type' => $conversation->type,
                    'avatar_url' => new GetAvatarAction()->handle($otherParticipants->first()),
                    'participants' => $conversation->participants->map(fn (User $participant): array => [
                        'id' => $participant->id,
                        'name' => $participant->name,
                        'avatar_url' => new GetAvatarAction()->handle($participant),
                    ]),
                    'last_message' => $lastMessage ? [
                        'id' => $lastMessage->id,
                        'content' => $lastMessage->content,
                        'type' => $lastMessage->type,
                        'created_at' => $lastMessage->created_at,
                        'user' => [
                            'id' => $lastMessage->user->id,
                            'name' => $lastMessage->user->name,
                            'avatar_url' => new GetAvatarAction()->handle($lastMessage->user),
                        ],
                    ] : null,
                    'last_message_at' => $conversation->last_message_at,
                    'unread_count' => $conversation->messages()
                        ->where('user_id', '!=', $user->id)
                        ->whereNull('read_at')
                        ->count(),
                ];
            });

        return response()->json($conversations);
    }

    /**
     * Store a new conversation.
     *
     * @throws Throwable
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:direct,group',
            'participants' => 'required|array|min:1',
            'participants.*' => 'exists:users,id',
            'title' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $participantIds = collect($request->participants)->filter(fn ($id): bool => $id !== $user->id)->values();

        if ($request->type === 'direct' && $participantIds->count() !== 1) {
            return response()->json(['error' => 'Direct conversations must have exactly one other participant'], 422);
        }

        if ($request->type === 'direct') {
            $existingConversation = Conversation::query()->directConversation(user1: $user, user2: User::find($participantIds->first()))
                ->first();
            if ($existingConversation) {
                return response()->json([
                    'id' => $existingConversation->id,
                    'message' => 'Conversation already exists',
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $conversation = Conversation::create([
                'title' => $request->title,
                'type' => $request->type,
                'created_by' => $user->id,
            ]);

            $allParticipants = $participantIds->concat([$user->id]);
            $conversation->participants()->attach($allParticipants->mapWithKeys(fn ($id): array => [
                $id => [
                    'joined_at' => now(),
                    'is_admin' => $id === $user->id,
                ],
            ]));

            DB::commit();

            // Broadcast conversation created to each participant via their private user channel
            $conversation->load('participants');
            foreach ($conversation->participants as $participant) {
                ConversationCreated::dispatch($conversation, $participant);
            }

            return response()->json([
                'id' => $conversation->id,
                'message' => 'Conversation created successfully',
            ], 201);
        } catch (Exception) {
            DB::rollBack();

            return response()->json(['error' => 'Failed to create conversation'], 500);
        }
    }

    /**
     * Display the specified conversation.
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();

        try {
            $conversation = Conversation::query()->forUser(user: $user)
                ->with(['participants', 'messages.user'])
                ->findOrFail($id);

            $messages = $conversation->messages()
                ->with('user')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn ($message): array => [
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
                ]);

            return response()->json([
                'id' => $conversation->id,
                'title' => $conversation->title,
                'type' => $conversation->type,
                'participants' => $conversation->participants->map(fn (User $participant): array => [
                    'id' => $participant->id,
                    'name' => $participant->name,
                    'avatar_url' => new GetAvatarAction()->handle($participant),
                ]),
                'messages' => $messages,
            ]);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }
    }

    /**
     * Remove the specified conversation.
     */
    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();

        try {
            $conversation = Conversation::query()->forUser(user: $user)->findOrFail($id);

            if ($conversation->creator->id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $conversation->delete();

            return response()->json(['message' => 'Conversation deleted successfully']);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }
    }
}
