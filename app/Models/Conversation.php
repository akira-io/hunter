<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ConversationFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Conversation extends Model
{
    /** @use HasFactory<ConversationFactory> */
    use HasFactory;

    protected $fillable
        = [
            'title',
            'type',
            'created_by',
            'last_message_at',
        ];

    /**
     * Scope a query to only include conversations for a given user.
     */
    #[Scope]
    public static function forUser(Builder $query, User $user): Builder
    {

        return $query->whereHas('participants', function ($q) use ($user): void {

            $q->where('user_id', $user->id);
        });
    }

    /**
     * Scope a query to direct conversation between two users.
     */
    #[Scope]
    public function directConversation(Builder $query, User $user1, User $user2): Builder
    {

        return $query->where('type', 'direct')
            ->whereHas('participants', function ($q) use ($user1): void {

                $q->where('user_id', $user1->id);
            })
            ->whereHas('participants', function ($q) use ($user2): void {

                $q->where('user_id', $user2->id);
            })
            ->has('participants', '=', 2);
    }

    /**
     * Get the creator of the conversation.
     *
     * @return BelongsTo <User, $this>
     */
    public function creator(): BelongsTo
    {

        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the messages for the conversation.
     *
     * @return HasMany <Message, $this>
     */
    public function messages(): HasMany
    {

        return $this->hasMany(Message::class);
    }

    /**
     * Get the participants of the conversation.
     *
     * @return BelongsToMany <User, $this>
     */
    public function participants(): BelongsToMany
    {

        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot(['joined_at', 'last_read_at', 'is_admin'])
            ->withTimestamps();
    }

    /**
     * Get the latest message for the conversation.
     *
     * @return HasMany <Message, $this>
     */
    public function latestMessage(): HasMany
    {

        return $this->messages()->latest();
    }

    /**
     *  Get the attributes that should be cast.
     *
     * @return string[]
     */
    protected function casts(): array
    {

        return [
            'last_message_at' => 'datetime',
        ];
    }
}
