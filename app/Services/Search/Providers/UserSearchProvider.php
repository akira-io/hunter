<?php

declare(strict_types=1);

namespace App\Services\Search\Providers;

use App\Contracts\Search\GlobalSearchable;
use App\DataTransferObjects\Search\SearchResult;
use App\Models\User;
use App\Services\Search\Concerns\HasScoutSearch;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Database\Eloquent\Model;

/**
 * Search provider for users/hunters in the global search.
 *
 * Enables searching for users by name, username, location, bio, and skills
 * using Laravel Scout. Results include user profile information and link
 * to their public profile page.
 *
 * @implements GlobalSearchable<User>
 */
#[Singleton]
final class UserSearchProvider implements GlobalSearchable
{
    use HasScoutSearch;

    /**
     * Get the search type identifier.
     *
     * @return string The type identifier used in search results
     */
    public function getType(): string
    {
        return 'users';
    }

    /**
     * Get the display label for this search type.
     *
     * @return string The label shown in search UI
     */
    public function getLabel(): string
    {
        return 'Hunters';
    }

    /**
     * Get the icon for this search type.
     *
     * @return string The icon name used in search UI
     */
    public function getIcon(): string
    {
        return 'user';
    }

    /**
     * Get the display priority for results.
     *
     * @return int Lower values appear first (1 = highest priority)
     */
    public function getPriority(): int
    {
        return 1;
    }

    /**
     * Get the User model class name.
     *
     * @return string The fully qualified User model class
     */
    public function getModelClass(): string
    {
        return User::class;
    }

    /**
     * Transform a User model into a search result DTO.
     *
     * @param  User  $model
     */
    public function mapToDto(Model $model): SearchResult
    {
        return new SearchResult(
            id: (string) $model->id,
            title: $model->name,
            subtitle: "@{$model->user_name}",
            description: $model->location,
            image: $model->avatar_url,
            url: $this->getRedirectUrl($model),
            metadata: [
                'bio' => $model->bio,
                'skills' => $model->skills,
                'location' => $model->location,
            ],
        );
    }

    /**
     * Generate the public profile URL for a user.
     *
     * @param  User  $model
     */
    public function buildRedirectUrl(Model $model): string
    {
        return route('public.profile.show', ['user' => $model->id]);
    }
}
