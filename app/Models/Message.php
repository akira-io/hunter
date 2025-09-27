<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property-read  int $id
 * @property-read  int $conversation_id
 * @property-read  int $user_id
 * @property-read  string $content
 * @property-read  string $type
 * @property-read  array<string,mixed>|null $metadata
 * @property-read  Carbon|null $read_at
 * @property-read  Carbon $created_at
 * @property-read  Carbon $updated_at
 * @property-read  Conversation $conversation
 * @property-read  User $user
 */
final class Message extends Model
{
    /** @use HasFactory<MessageFactory> */
    use HasFactory;

    protected $fillable
        = [
            'conversation_id',
            'user_id',
            'content',
            'type',
            'metadata',
            'read_at',
        ];

    /**
     * Conversation relationship
     *
     * @return BelongsTo <Conversation, $this>
     */
    public function conversation(): BelongsTo
    {

        return $this->belongsTo(Conversation::class);
    }

    /**
     * User relationship
     *
     * @return BelongsTo <User, $this>
     */
    public function user(): BelongsTo
    {

        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include unread messages for a given user.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    public function unread(Builder $query, User $user): Builder
    {
        /** @var Builder<self> $result */
        $result = $query->whereNull('read_at')
            ->where('user_id', '!=', $user->getAttribute('id'));

        return $result;
    }

    /**
     * Mark the message as read.
     */
    public function markAsRead(): void
    {

        $this->update(['read_at' => now()]);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return string[]
     */
    protected function casts(): array
    {

        return [
            'metadata' => 'array',
            'read_at' => 'datetime',
        ];
    }
}
