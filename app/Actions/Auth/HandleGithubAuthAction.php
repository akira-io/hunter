<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Exceptions\AccountAlreadyLinkedException;
use App\Models\User;
use App\ValueObjects\GithubUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Two\User as SocialiteUser;

final readonly class HandleGithubAuthAction
{
    /**
     * Handle GitHub authentication for a user.
     *
     * This action implements the logic to either create a new user or update an existing one
     * based on email-first authentication strategy for cross-provider compatibility.
     *
     * @throws AccountAlreadyLinkedException
     */
    public function handle(SocialiteUser $githubUser): User
    {
        /** @var array<string, mixed> $githubUserData */
        $githubUserData = GithubUser::from($githubUser)->toArray();

        /** @var string $email */
        $email = $githubUserData['email'] ?? '';

        /** @var User|null $authenticatedUser */
        $authenticatedUser = Auth::user();

        /** @var User|null $userByGithubId */
        $userByGithubId = $this->findUserByGithubId($githubUser->getId());

        if ($authenticatedUser instanceof User) {

            if ($userByGithubId instanceof User && $userByGithubId->id !== $authenticatedUser->id) {
                Log::warning('Attempted to link GitHub account already associated with another user', [
                    'authenticated_user_id' => $authenticatedUser->id,
                    'github_account_user_id' => $userByGithubId->id,
                    'github_id' => $githubUser->getId(),
                ]);

                throw new AccountAlreadyLinkedException('GitHub');
            }

            return $this->linkGithubToExistingUser($authenticatedUser, $githubUserData);
        }

        /** @var User|null $user */
        $user = $this->findUserByEmail($email);

        if ($user instanceof User) {
            return $this->linkGithubToExistingUser($user, $githubUserData);
        }

        if ($userByGithubId instanceof User) {
            return $this->updateExistingGithubUser($userByGithubId, $githubUserData);
        }

        return $this->createNewUser($githubUserData);
    }

    /**
     * Find user by email address.
     */
    private function findUserByEmail(string $email): ?User
    {
        /** @var User|null $user */
        $user = User::query()->firstWhere('email', $email);

        return $user;
    }

    /**
     * Find user by GitHub ID.
     */
    private function findUserByGithubId(string|int $githubId): ?User
    {
        /** @var User|null $user */
        $user = User::query()->firstWhere('github_id', $githubId);

        return $user;
    }

    /**
     * Link GitHub account to existing user (likely from Google auth).
     */
    /**
     * @param  array<string, mixed>  $githubUserData
     */
    private function linkGithubToExistingUser(User $user, array $githubUserData): User
    {
        /** @var array<string, mixed> $updateData */
        $updateData = [
            'github_id' => $githubUserData['github_id'],
            'github_token' => $githubUserData['github_token'],
            'github_refresh_token' => $githubUserData['github_refresh_token'],
            'user_name' => $githubUserData['user_name'],
            'github_url' => $githubUserData['github_url'],
        ];

        if (empty($user->bio)) {
            $updateData['bio'] = $githubUserData['bio'];
        }

        if (empty($user->location)) {
            $updateData['location'] = $githubUserData['location'];
        }

        if (empty($user->avatar_url)) {
            $updateData['avatar_url'] = $githubUserData['avatar_url'];
        }

        $user->update($updateData);

        return $user;
    }

    /**
     * Update existing GitHub user with fresh data.
     */
    /**
     * @param  array<string, mixed>  $githubUserData
     */
    private function updateExistingGithubUser(User $user, array $githubUserData): User
    {
        $githubUserData['bio'] = $user->bio ?? $githubUserData['bio'];
        $githubUserData['location'] = $user->location ?? $githubUserData['location'];
        $githubUserData['avatar_url'] = $user->avatar_url ?? $githubUserData['avatar_url'];
        $githubUserData['email'] = $user->email ?? $githubUserData['email'];

        /** @var array<string, mixed> $updateData */
        $updateData = $githubUserData;
        $user->update($updateData);

        return $user;
    }

    /**
     * Create a new user from GitHub data.
     */
    /**
     * @param  array<string, mixed>  $githubUserData
     */
    private function createNewUser(array $githubUserData): User
    {
        /** @var User $user */
        $user = User::query()->create($githubUserData);

        return $user;
    }
}
