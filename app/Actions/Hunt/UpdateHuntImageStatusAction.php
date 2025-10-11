<?php

declare(strict_types=1);

namespace App\Actions\Hunt;

use App\Enums\HuntImageProcessingStatus;
use App\Models\Hunt;

final readonly class UpdateHuntImageStatusAction
{
    /**
     * Update the hunt image processing status.
     */
    public function handle(Hunt $hunt, HuntImageProcessingStatus $status): void
    {
        $hunt->update([
            'image_processing_status' => $status->value,
        ]);
    }
}
