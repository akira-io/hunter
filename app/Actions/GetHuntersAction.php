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

        $user?->attachFollowStatus($collection);

        /** @var Collection<int, array<string, mixed>> $hunters */
        $hunters = $collection->map(
            function (User $userModel) use ($user): array {
                $data = [
                    'id' => $userModel->id,
                    'name' => $userModel->name,
                    'email' => $userModel->email,
                    'avatar_url' => $userModel->getMedia('profile_avatar')->last()?->getUrl() ?? $userModel->avatar_url,
                    'background_image_url' => $userModel->getMedia('profile_background')->last()?->getUrl() ?? 'https://images.unsplash.com/photo-1746768934151-8c5cb84bcf11?w=500&auto=format&fit=crop&q=60',
                    'location' => $userModel->location,
                    'bio' => $userModel->bio,
                    'user_name' => $userModel->user_name,
                    'email_verified_at' => $userModel->email_verified_at,
                    'created_at' => $userModel->created_at,
                    'updated_at' => $userModel->updated_at,
                    'skills' => $userModel->skills,
                    'github_url' => $userModel->github_url,
                    'twitter_url' => $userModel->twitter_url,
                    'linkedin_url' => $userModel->linkedin_url,
                    'bluesky_url' => $userModel->bluesky_url,
                    'website_url' => $userModel->website_url,
                    'youtube_url' => $userModel->youtube_url,
                ];

                // Only include follow status if user is authenticated
                if ($user) {
                    $data['has_followed'] = $userModel->has_followed ?? false;
                    $data['followed_at'] = $userModel->followed_at ?? null;
                    $data['follow_accepted_at'] = $userModel->follow_accepted_at ?? null;
                }

                return $data;
            }
        );

        /** @var Collection<int, array<string, mixed>> $hunters */
        return [$hunters, $paginator];
    }
}
