<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\User\GetAvatarAction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Log;

final readonly class FollowedHuntersController
{
    /**
     * Get the followed hunters.
     */
    public function index(): JsonResponse
    {
        Log::info('🔍 FollowedHuntersController: Requisição recebida');

        $user = Auth::user();

        if (! $user) {
            Log::warning('🔍 FollowedHuntersController: Usuário não autenticado');

            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Log::info('🔍 FollowedHuntersController: Usuário autenticado', ['user_id' => $user->id]);

        $followedHunters = User::whereHas('followers', function ($query) use ($user): void {
            $query->where('user_id', $user->id)
                ->whereNotNull('accepted_at');
        })
            ->select(['id', 'name', 'user_name', 'avatar_url'])
            ->orderBy('name')
            ->get()
            ->map(fn (User $hunter): array => [
                'id' => $hunter->id,
                'name' => $hunter->name,
                'username' => $hunter->user_name,
                'avatar_url' => new GetAvatarAction()->handle($hunter),
                'level' => null,
                'is_online' => false,
            ]);

        Log::info('🔍 FollowedHuntersController: Hunters encontrados', ['count' => $followedHunters->count()]);

        return response()->json($followedHunters);
    }
}
