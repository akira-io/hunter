<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\ConversationFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read  User $creator
 * @property-read  string $title
 * @property-read  string $type
 * @property-read  int $id
 * @property-read  Carbon|\Illuminate\Support\Carbon|null $last_message_at
 * @property-read  int $unread_count
 * @property-read  int $participants_count
 * @property-read  int $messages_count
 * @property-read  Collection<int, Message> $messages
 * @property-read  Collection<int, User> $participants
 * @property-read  Message|null $latest_message
 * @property-read  Carbon $created_at
 * @property-read  Carbon $updated_at
 * @property-read  int $created_by
 */
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
     * Scope a query to direct conversation between two users.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    public function directConversation(Builder $query, User $user1, User $user2): Builder
    {

        return $query->where('type', 'direct')
            ->whereHas('participants', function (Builder $q) use ($user1): void {

                $q->where('user_id', $user1->id);
            })
            ->whereHas('participants', function (Builder $q) use ($user2): void {

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
     * @return HasOne <Message, $this>
     */
    public function latestMessage(): HasOne
    {

        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Scope a query to only include conversations for a given user.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    public function forUser(Builder $query, User $user): Builder
    {

        return $query->whereHas('participants', function (Builder $q) use ($user): void {
            $q->where('user_id', $user->id);
        });
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
