<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\User\GetAvatarAction;
use App\Events\ConversationCreated;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        if (! $user instanceof User) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        /** @var Collection<int, Conversation> $conversationsCollection */
        $conversationsCollection = $user->conversations()
            ->with([
                'participants',
                'messages' => function ($query): void {
                    // @phpstan-ignore-next-line
                    $query->latest()->limit(1)->with('user');
                },
            ])
            ->orderBy('last_message_at', 'desc')
            ->get();

        $conversations = $conversationsCollection->map(function (Conversation $conversation) use ($user): array {
            /** @var Collection<int, Message> $messages */
            $messages = $conversation->getRelation('messages');
            $lastMessage = $messages->first();

            /** @var Collection<int, User> $participants */
            $participants = $conversation->getRelation('participants');
            $userId = $user->id;
            $otherParticipants = $participants->where('id', '!=', $userId);

            /** @var User $otherParticipant */
            $otherParticipant = $otherParticipants->first();

            return [
                'id' => $conversation->id,
                'title' => $conversation->title ?: $otherParticipants->pluck('name')->join(', '),
                'type' => $conversation->type,
                'avatar_url' => new GetAvatarAction()->handle($otherParticipant),
                'participants' => $participants->map(fn (User $participant): array => [
                    'id' => $participant->id,
                    'name' => $participant->id,
                    'avatar_url' => new GetAvatarAction()->handle($participant),
                ]),
                'last_message' => ($lastMessage instanceof Message) ? $this->formatMessage($lastMessage) : null,
                'last_message_at' => $conversation->last_message_at,
                'unread_count' => $conversation->messages()
                    ->where('user_id', '!=', $userId)
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
        if (! $user instanceof User) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        /** @var array<int> $participantsInput */
        $participantsInput = $request->input('participants', []);
        $participantIds = collect($participantsInput)->filter(fn (int $id): bool => $id !== $user->getAttribute('id'))->values();

        if ($request->input('type') === 'direct' && $participantIds->count() !== 1) {
            return response()->json(['error' => 'Direct conversations must have exactly one other participant'], 422);
        }

        if ($request->input('type') === 'direct') {
            $otherUser = User::query()->find($participantIds->first());
            if (! $otherUser instanceof User) {
                return response()->json(['error' => 'Invalid participant'], 422);
            }
            $existingConversation = Conversation::query()
                ->where('type', 'direct')
                ->whereHas('participants', function (Builder $q) use ($user): void {
                    $q->where('user_id', $user->getAttribute('id'));
                })
                ->whereHas('participants', function (Builder $q) use ($otherUser): void {
                    $q->where('user_id', $otherUser->getAttribute('id'));
                })
                ->has('participants', '=', 2)
                ->first();
            if ($existingConversation) {
                return response()->json([
                    'id' => $existingConversation->getAttribute('id'),
                    'message' => 'Conversation already exists',
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $conversation = Conversation::query()->create([
                'title' => $request->input('title'),
                'type' => $request->input('type'),
                'created_by' => $user->getAttribute('id'),
            ]);

            $allParticipants = $participantIds->concat([$user->getAttribute('id')]);
            /** @var array<int, array{joined_at: Carbon, is_admin: bool}> $attachData */
            $attachData = [];
            $userIdValue = $user->getAttribute('id');
            $userIdInt = is_numeric($userIdValue) ? (int) $userIdValue : 0;
            foreach ($allParticipants as $id) {
                if (is_numeric($id)) {
                    $intId = (int) $id;
                    $attachData[$intId] = [
                        'joined_at' => now(),
                        'is_admin' => $intId === $userIdInt,
                    ];
                }
            }
            $conversation->participants()->attach($attachData);

            DB::commit();

            // Broadcast conversation created to each participant via their private user channel
            $conversation->load('participants');
            /** @var Collection<int, User> $participants */
            $participants = $conversation->getRelation('participants');
            foreach ($participants as $participant) {
                ConversationCreated::dispatch($conversation, $participant);
            }

            return response()->json([
                'id' => $conversation->getAttribute('id'),
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
        if (! $user instanceof User) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            /** @var Conversation $conversation */
            $conversation = Conversation::query()
                ->whereHas('participants', function (Builder $q) use ($user): void {
                    $q->where('user_id', $user->getAttribute('id'));
                })
                ->findOrFail($id);

            /** @var Collection<int, Message> $messagesCollection */
            $messagesCollection = $conversation->messages()
                ->with('user')
                ->orderBy('created_at', 'asc')
                ->get();

            $messages = $messagesCollection->map(fn (Message $message): array => $this->formatMessage($message));

            /** @var Collection<int, User> $participants */
            $participants = $conversation->participants;

            return response()->json([
                'id' => $conversation->id,
                'title' => $conversation->title,
                'type' => $conversation->type,
                'participants' => $participants->map(fn (User $participant): array => [
                    'id' => $participant->id,
                    'name' => $participant->name,
                    'avatar_url' => new GetAvatarAction()->handle($participant),
                ]),
                'messages' => $messages,
                'unread_count' => $conversation->messages()
                    ->where('user_id', '!=', $user->getAttribute('id'))
                    ->whereNull('read_at')
                    ->count(),
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
        if (! $user instanceof User) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            /** @var Conversation $conversation */
            $conversation = Conversation::query()
                ->whereHas('participants', function (Builder $q) use ($user): void {
                    $q->where('user_id', $user->getAttribute('id'));
                })
                ->with('creator')
                ->findOrFail($id);

            $creator = $conversation->getRelation('creator');
            if ($creator instanceof User && $creator->getAttribute('id') !== $user->getAttribute('id')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $conversation->delete();

            return response()->json(['message' => 'Conversation deleted successfully']);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }
    }

    /**
     * Format a message for API response.
     *
     * @return array{id: mixed, content: mixed, type: mixed, created_at: mixed, user: array{id: mixed, name: mixed, avatar_url: string|null}}
     */
    private function formatMessage(Message $message): array
    {
        $user = $message->user;

        return [
            'id' => $message->id,
            'content' => $message->content,
            'type' => $message->type,
            'metadata' => $message->metadata,
            'created_at' => $message->created_at,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar_url' => new GetAvatarAction()->handle($user),
            ],
        ];
    }
}
