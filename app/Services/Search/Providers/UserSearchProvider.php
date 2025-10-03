<?php

declare(strict_types=1);

namespace App\Services\Search\Providers;

use App\Contracts\Search\GlobalSearchable;
use App\DataTransferObjects\Search\SearchResultDto;
use App\Models\User;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;

#[Singleton]
final readonly class UserSearchProvider implements GlobalSearchable
{
    public function getType(): string
    {
        return 'users';
    }

    public function getLabel(): string
    {
        return 'Hunters';
    }

    public function getIcon(): string
    {
        return 'user';
    }

    public function getPriority(): int
    {
        return 1;
    }

    public function getRedirectUrl(Model $model): string
    {
        if (! $model instanceof User) {
            throw new InvalidArgumentException('Model must be an instance of User');
        }

        return route('public.profile.show', ['user' => $model->id]);
    }

    /**
     * @return Collection<int, SearchResultDto>
     */
    public function search(string $query, int $limit = 5): Collection
    {
        return User::search($query)
            ->take($limit)
            ->get()
            ->map(fn (User $user) => new SearchResultDto(
                id: (string) $user->id,
                title: $user->name,
                subtitle: "@{$user->user_name}",
                description: $user->location,
                image: $user->avatar_url,
                url: $this->getRedirectUrl($user),
                metadata: [
                    'bio' => $user->bio,
                    'skills' => $user->skills,
                    'location' => $user->location,
                ],
            ));
    }
}
