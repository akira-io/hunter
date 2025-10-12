<?php

declare(strict_types=1);

namespace App\Actions\Hunt;

use App\Enums\HuntImageProcessingStatus;
use App\Events\HuntImageProcessed;
use App\Models\Hunt;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

final readonly class ProcessHuntImageAction
{
    /**
     * Create a new instance of the action.
     */
    public function __construct(
        private UpdateHuntImageStatusAction $updateStatusAction
    ) {}

    /**
     * Process and store the hunt image.
     */
    public function handle(Hunt $hunt, UploadedFile $image): void
    {
        try {
            $this->updateStatusAction->handle($hunt, HuntImageProcessingStatus::Processing);

            $hunt->addMedia($image)->toMediaCollection('hunts');

            $this->updateStatusAction->handle($hunt, HuntImageProcessingStatus::Completed);

            $hunt->refresh();

            broadcast(new HuntImageProcessed($hunt));
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            $this->updateStatusAction->handle($hunt, HuntImageProcessingStatus::Failed);

            Log::error('Failed to process hunt image', [
                'hunt_id' => $hunt->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
        // @codeCoverageIgnoreEnd
    }
}
