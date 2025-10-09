<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Hunt\ProcessHuntImageAction;
use App\Enums\HuntImageProcessingStatus;
use App\Models\Hunt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class ProcessHuntImage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Hunt $hunt,
        public string $imagePath,
        public string $imageName,
        public string $imageMimeType
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ProcessHuntImageAction $action): void
    {
        // Recreate the UploadedFile from the stored temp file
        $tempPath = Storage::disk('local')->path($this->imagePath);
        $image = new UploadedFile(
            $tempPath,
            $this->imageName,
            $this->imageMimeType,
            null,
            true
        );

        $action->handle($this->hunt, $image);

        // Clean up temp file
        Storage::disk('local')->delete($this->imagePath);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        $this->hunt->update([
            'image_processing_status' => HuntImageProcessingStatus::Failed->value,
        ]);

        // Clean up temp file on failure
        Storage::disk('local')->delete($this->imagePath);
    }
}
