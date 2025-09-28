<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Chat\CreateConversationAction;
use App\Actions\Chat\DeleteConversationAction;
use App\Actions\Chat\GetConversationMessagesAction;
use App\Actions\Chat\GetConversationsAction;
use App\Http\Requests\Chat\CreateConversationRequest;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;

final readonly class ConversationController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private GetConversationsAction $getConversationsAction,
        private CreateConversationAction $createConversationAction,
        private GetConversationMessagesAction $getConversationMessagesAction,
        private DeleteConversationAction $deleteConversationAction
    ) {}

    /**
     * Get the conversations of the authenticated user.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $conversations = $this->getConversationsAction->handle($user);

        return response()->json($conversations);
    }

    /**
     * Store a new conversation.
     *
     * @throws Throwable
     */
    public function store(CreateConversationRequest $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $result = $this->createConversationAction->handle(
                $user,
                $request->getType(),
                $request->getParticipants(),
                $request->getTitle()
            );

            $statusCode = $result['existing'] ? 200 : 201;

            return response()->json([
                'id' => $result['id'],
                'message' => $result['message'],
            ], $statusCode);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'Direct conversations must have exactly one other participant') ||
                str_contains($e->getMessage(), 'Invalid participant')) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return response()->json(['error' => $e->getMessage()], 500);
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
            $conversationData = $this->getConversationMessagesAction->handle($user, $id);

            return response()->json($conversationData);
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
            $this->deleteConversationAction->handle($user, $id);

            return response()->json(['message' => 'Conversation deleted successfully']);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Conversation not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }
}
