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

            // Refresh hunt to ensure we have the latest data including media
            $hunt->refresh();

            broadcast(new HuntImageProcessed($hunt));

            Log::info('Hunt image processed successfully', [
                'hunt_id' => $hunt->id,
                'image_url' => $hunt->getFirstMediaUrl('hunts'),
                'status' => $hunt->image_processing_status->value,
                'broadcasting_event' => 'hunt.image.processed',
            ]);
        } catch (Exception $e) {
            $this->updateStatusAction->handle($hunt, HuntImageProcessingStatus::Failed);

            Log::error('Failed to process hunt image', [
                'hunt_id' => $hunt->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
