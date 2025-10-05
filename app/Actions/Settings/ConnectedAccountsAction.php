<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\User;

final readonly class ConnectedAccountsAction
{
    /**
     * Get the connected OAuth provider accounts for the user.
     *
     * @return array<string, bool>
     */
    public function handle(User $user): array
    {

        return [
            'github' => ! empty($user->github_id),
            'google' => ! empty($user->google_id ?? null),
        ];
    }
}
