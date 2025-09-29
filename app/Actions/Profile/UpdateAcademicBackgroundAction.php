<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\DataTransferObjects\Profile\AcademicBackgroundData;
use App\Models\AcademicBackground;
use App\Models\User;

final readonly class UpdateAcademicBackgroundAction
{
    /**
     * Create academic background for user.
     */
    public function handle(User $user, AcademicBackgroundData $academicData): AcademicBackground
    {
        return $user->academicBackgrounds()->create($academicData->toArray());
    }
}
