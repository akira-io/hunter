<?php

declare(strict_types=1);

namespace App\Actions\Hunt;

use App\DataTransferObjects\Hunt\CreateHuntData;
use App\Enums\HuntImageProcessingStatus;
use App\Jobs\ProcessHuntImage;
use App\Models\Hunt;
use App\Models\User;
use App\Notifications\HuntPublishedNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;

final readonly class CreateHuntAction
{
    /**
     * Create a new hunt for the given user.
     */
    public function handle(User $user, CreateHuntData $huntData): Hunt
    {
        $huntAttributes = $huntData->toArray();

        if ($huntData->image instanceof UploadedFile) {
            $huntAttributes['image_processing_status'] = HuntImageProcessingStatus::Pending->value;
        }

        $hunt = $user->hunts()->create($huntAttributes);

        if ($huntData->image instanceof UploadedFile) {
            $tempPath = $huntData->image->store('temp', 'local');

            ProcessHuntImage::dispatch(
                $hunt,
                $tempPath,
                $huntData->image->getClientOriginalName(),
                $huntData->image->getMimeType()
            );
        }

        $this->notifyFollowers($user, $hunt);

        return $hunt;
    }

    /**
     * Notify all followers about the new hunt.
     */
    private function notifyFollowers(User $author, Hunt $hunt): void
    {
        $followers = User::query()
            ->whereHas('followings', function (Builder $query) use ($author): void {
                $query->where('followable_id', $author->id)
                    ->where('followable_type', User::class)
                    ->whereNotNull('accepted_at');
            })
            ->get();

        Notification::send($followers, new HuntPublishedNotification($hunt, $author));
    }
}
