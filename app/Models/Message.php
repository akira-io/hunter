<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     */
    #[Scope]
    public function unread(Builder $query, User $user): Builder
    {

        return $query->whereNull('read_at')
            ->where('user_id', '!=', $user->id);
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
