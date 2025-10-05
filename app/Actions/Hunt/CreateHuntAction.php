<?php

declare(strict_types=1);

namespace App\Actions\Hunt;

use App\DataTransferObjects\Hunt\CreateHuntData;
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
        $hunt = $user->hunts()->create($huntData->toArray());

        if ($huntData->image instanceof UploadedFile) {
            $hunt->addMedia($huntData->image)->toMediaCollection('hunts');
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
