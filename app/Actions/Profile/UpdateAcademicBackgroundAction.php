<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\AcademicBackground;
use App\Models\User;

final readonly class UpdateAcademicBackgroundAction
{
    /**
     * Create academic background for user.
     *
     * @param  array<string, mixed>  $academicData
     */
    public function handle(User $user, array $academicData): AcademicBackground
    {
        return $user->academicBackgrounds()->create($academicData);
    }
}
