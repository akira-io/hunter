<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final readonly class GetHuntersAction
{
    /**
     * Handle the action of getting hunters.
     *
     * @return array{
     *     0: Collection<int, array<string, mixed>>,
     *     1: LengthAwarePaginator<int, User>
     * }
     */
    public function handle(Request $request, int $perPage = 15, ?User $user = null): array
    {
        /** @var User|null $user */
        $user = ($user instanceof User ? $user : $request->user()) ?: null;

        $query = $request->string('q')->value();

        $usersQuery = filled($query)
            ? User::search($query)
            : User::query()->inRandomOrder();

        /** @var LengthAwarePaginator<int, User> $paginator */
        $paginator = $usersQuery->paginate($perPage)->withQueryString();

        $paginator->getCollection()->load('academicBackgrounds'); // @phpstan-ignore-line
        /** @var Collection<int, User> $collection */
        $collection = $paginator->getCollection();

        /** @var Collection<int, array<string, mixed>> $hunters */
        $hunters = $collection->map(
            static fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->getMedia('profile_avatar')->last()?->getUrl() ?? $user->avatar_url,
                'background_image_url' => $user->getMedia('profile_background')->last()?->getUrl() ?? 'https://images.unsplash.com/photo-1746768934151-8c5cb84bcf11?w=500&auto=format&fit=crop&q=60',
                'location' => $user->location,
                'bio' => $user->bio,
                'user_name' => $user->user_name,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'skills' => $user->skills,
                'github_url' => $user->github_url,
                'twitter_url' => $user->twitter_url,
                'linkedin_url' => $user->linkedin_url,
                'bluesky_url' => $user->bluesky_url,
                'website_url' => $user->website_url,
                'youtube_url' => $user->youtube_url,
            ]
        );

        $hunters = $user
            ? $user->attachFollowStatus($hunters)
            : $hunters;

        /** @var Collection<int, array<string, mixed>> $hunters */
        return [$hunters, $paginator];
    }
}
