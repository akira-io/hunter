<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Chat\ValidateUserIsParticipantAction;
use App\Http\Resources\CurrentUserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Middleware(['auth', 'verified'])]
#[Prefix('chat')]
final readonly class ChatController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private ValidateUserIsParticipantAction $validateUserIsParticipantAction
    ) {}

    /**
     * Display the chat index page with list of conversations.
     */
    #[Get('/', name: 'chat.index')]
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('chat/index', [
            'currentUser' => CurrentUserResource::make($user)->resolve(),
        ]);
    }

    /**
     * Display a specific conversation.
     */
    #[Get('/{conversation}', name: 'chat.show')]
    public function show(Request $request, int $conversation): Response
    {
        /** @var User $user */
        $user = $request->user();

        $this->validateUserIsParticipantAction->handle($user, $conversation);

        return Inertia::render('chat/desktop', [
            'conversationId' => $conversation,
            'currentUser' => CurrentUserResource::make($user)->resolve(),
        ]);
    }
}
