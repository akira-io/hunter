<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\User\GetAvatarAction;
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
        private GetAvatarAction $getAvatarAction
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
            'currentUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar_url' => $this->getAvatarAction->handle($user),
            ],
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

        return Inertia::render('chat/desktop', [
            'conversationId' => $conversation,
            'currentUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar_url' => $this->getAvatarAction->handle($user),
            ],
        ]);
    }
}
