<?php

declare(strict_types=1);

namespace App\Actions\Hunt;

use App\Models\Hunt;

final readonly class DeleteHuntAction
{
    /**
     * Delete the given hunt.
     */
    public function handle(Hunt $hunt): ?bool
    {
        return $hunt->delete();
    }
}
