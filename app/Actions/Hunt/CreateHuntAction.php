<?php

declare(strict_types=1);

namespace App\Actions\Hunt;

use App\DataTransferObjects\Hunt\CreateHuntData;
use App\Models\Hunt;
use App\Models\User;

final readonly class CreateHuntAction
{
    /**
     * Create a new hunt for the given user.
     */
    public function handle(User $user, CreateHuntData $huntData): Hunt
    {
        $hunt = $user->hunts()->create($huntData->toArray());

        if ($huntData->image !== null) {
            $hunt->addMedia($huntData->image)->toMediaCollection('hunts');
        }

        return $hunt;
    }
}
