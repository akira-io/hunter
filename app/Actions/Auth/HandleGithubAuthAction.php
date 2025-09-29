<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use App\ValueObjects\GithubUser;
use Laravel\Socialite\Two\User as SocialiteUser;

final readonly class HandleGithubAuthAction
{
    /**
     * Handle GitHub authentication for a user.
     *
     * This action implements the logic to either create a new user or update an existing one
     * based on email-first authentication strategy for cross-provider compatibility.
     */
    public function handle(SocialiteUser $githubUser): User
    {
        $githubUserData = GithubUser::from($githubUser)->toArray();

        /** @var string $email */
        $email = $githubUserData['email'] ?? '';
        $user = $this->findUserByEmail($email);

        if ($user instanceof User) {
            return $this->linkGithubToExistingUser($user, $githubUserData);
        }

        $user = $this->findUserByGithubId($githubUser->getId());

        if ($user instanceof User) {
            return $this->updateExistingGithubUser($user, $githubUserData);
        }

        return $this->createNewUser($githubUserData);
    }

    /**
     * Find user by email address.
     */
    private function findUserByEmail(string $email): ?User
    {
        return User::query()->firstWhere('email', $email);
    }

    /**
     * Find user by GitHub ID.
     */
    private function findUserByGithubId(string $githubId): ?User
    {
        return User::query()->firstWhere('github_id', $githubId);
    }

    /**
     * Link GitHub account to existing user (likely from Google auth).
     */
    /**
     * @param  array<string, mixed>  $githubUserData
     */
    private function linkGithubToExistingUser(User $user, array $githubUserData): User
    {
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
        return User::query()->create($githubUserData);
    }
}
