<?php

declare(strict_types=1);

namespace App\Actions\Hunt;

use App\Models\Hunt;
use App\Models\User;
use Illuminate\Http\UploadedFile;

final readonly class CreateHuntAction
{
    /**
     * Create a new hunt for the given user.
     *
     * @param  array<string, mixed>  $huntData
     */
    public function handle(User $user, array $huntData, ?UploadedFile $image = null): Hunt
    {
        $hunt = $user->hunts()->create($huntData);

        if ($image !== null) {
            $hunt->addMedia($image)->toMediaCollection('hunts');
        }

        return $hunt;
    }
}
