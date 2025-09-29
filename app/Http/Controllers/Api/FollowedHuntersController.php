<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\User\GetAvatarAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;

final readonly class FollowedHuntersController
{
    /**
     * Get the followed hunters.
     */
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Collection<int, User> $followedHuntersCollection */
        $followedHuntersCollection = User::query()->whereHas('followers', function (Builder $query) use ($user): void {
            $query->where('user_id', $user->id)
                ->whereNotNull('accepted_at');
        })
            ->select(['id', 'name', 'user_name', 'avatar_url'])
            ->orderBy('name')
            ->get();

        /** @var SupportCollection<int, array{id: mixed, name: mixed, username: mixed, avatar_url: string|null, level: null, is_online: false}> $followedHunters */
        $followedHunters = $followedHuntersCollection->map(fn (User $hunter): array => [
            'id' => $hunter->id,
            'name' => $hunter->name,
            'username' => $hunter->user_name,
            'avatar_url' => new GetAvatarAction()->handle($hunter),
            'level' => null,
            'is_online' => false,
        ]);

        return response()->json($followedHunters);
    }
}
