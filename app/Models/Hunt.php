<?php

declare(strict_types=1);

namespace App\Models;

use Akira\Commentable\Concerns\Commentable;
use Akira\Commentable\Models\Comment;
use Akira\Likeable\Concerns\Likeable;
use App\Enums\HuntImageProcessingStatus;
use Database\Factories\HuntFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * The Hunt model.
 *
 * @property-read  string $id
 * @property-read  int $owner_id
 * @property-read  string|null $content
 * @property-read  bool $is_reported
 * @property-read  bool $is_pinned
 * @property-read  bool $is_ignored
 * @property-read  Carbon $created_at
 * @property-read  Carbon $updated_at
 * @property-read  bool $has_likes
 * @property-read  User $owner
 * @property MorphMany<Comment, $this> $comments
 * @property-read int $likes_count
 * @property-read bool $has_liked
 * @property HuntImageProcessingStatus|null $image_processing_status
 */
final class Hunt extends Model implements HasMedia
{
    use Commentable;

    /** @use HasFactory<HuntFactory> */
    use HasFactory;

    use InteractsWithMedia;
    use Likeable;
    use Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable
        = [
            'owner_id',
            'content',
            'is_reported',
            'is_pinned',
            'is_ignored',
            'views_count',
            'shares_count',
            'image_processing_status',
        ];

    /**
     * The hunt's owner.
     *
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {

        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Format the created_at date to a human-readable format.
     */
    public function toHumanDate(): Attribute
    {
        return Attribute::make(
            get: fn (string $value): string => $this->created_at->diffForHumans(),
        );
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'owner_id' => $this->owner_id,
            'owner_name' => $this->owner->name,
            'owner_username' => $this->owner->user_name,
            'created_at' => $this->created_at->timestamp,
        ];
    }

    /**
     * Increment the views count for the hunt.
     */
    public function incrementViews(): void
    {
        // Remove has_liked from attributes to prevent saving it
        unset($this->attributes['has_liked']);

        $this->views_count = ($this->views_count ?? 0) + 1;
        $this->saveQuietly();
    }

    /**
     * Increment the shares count for the hunt.
     */
    public function incrementShares(): void
    {
        $this->increment('shares_count');
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {

        return [
            'is_reported' => 'boolean',
            'is_pinned' => 'boolean',
            'is_ignored' => 'boolean',
            'created_at' => 'datetime',
            'views_count' => 'integer',
            'shares_count' => 'integer',
            'image_processing_status' => HuntImageProcessingStatus::class,
        ];
    }
}
