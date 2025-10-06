<?php

declare(strict_types=1);

namespace App\Models;

use Akira\Commentable\Concerns\Commenter;
use Akira\Followable\Concerns\Followable;
use Akira\Followable\Concerns\Follower;
use Akira\LaravelAuthLogs\Concerns\AuthLogs;
use Akira\Likeable\Concerns\Liker;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property-read int $id
 * @property-read  string $name
 * @property-read  string $email
 * @property-read  string $password
 * @property-read  string $remember_token
 * @property string|null $avatar_url
 * @property-read  string $location
 * @property-read  string $bio
 * @property-read  string $github_id
 * @property-read  string $github_token
 * @property-read  string $github_refresh_token
 * @property-read  string $user_name
 * @property string|null $email_verified_at
 * @property-read  CarbonImmutable $created_at
 * @property-read  CarbonImmutable $updated_at
 * @property-read  list<mixed> $skills
 * @property-read  HasMany<AcademicBackground,$this> $academicBackgrounds
 * @property-read  MorphMany<User, $this> $followers
 * @property-read  MorphMany<User, $this> $followings
 * @property-read  string|null $github_url
 * @property-read  string|null $twitter_url
 * @property-read  string|null $linkedin_url
 * @property-read  string|null $bluesky_url
 * @property-read  string|null $website_url
 * @property-read  string|null $youtube_url
 * @property-read  \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $unreadNotifications
 * @property-read CarbonInterface $onboarding_completed_at
 * @property-read bool $onboarding_completed
 *
 * @method void markAsRead()
 */
final class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use AuthLogs;
    use Commenter;
    use Followable;
    use Follower;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use InteractsWithMedia;
    use Liker;
    use Notifiable;
    use Searchable;

    /**
     * @var mixed|string
     */
    public mixed $background_image_url;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'remember_token',
        'avatar_url',
        'location',
        'bio',
        'github_id',
        'github_token',
        'github_refresh_token',
        'google_id',
        'google_token',
        'google_refresh_token',
        'user_name',
        'skills',
        'github_url',
        'twitter_url',
        'linkedin_url',
        'bluesky_url',
        'website_url',
        'youtube_url',
        'notification_settings',
        'privacy_settings',
        'onboarding_completed_at',
        'onboarding_completed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be searchable.
     *
     * @return array<string, list<mixed>|string>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'location' => $this->location,
            'user_name' => $this->user_name,
            'skills' => $this->skills,
        ];
    }

    /**
     * Professional education relationship
     *
     * @return HasMany<AcademicBackground, $this>
     */
    public function academicBackgrounds(): HasMany
    {

        return $this->hasMany(AcademicBackground::class, 'user_id');
    }

    /**
     * The user's hunts
     *
     * @return HasMany<Hunt, $this>
     */
    public function hunts(): HasMany
    {

        return $this->hasMany(Hunt::class, 'owner_id');
    }

    /**
     * Chat conversations that the user participates in
     *
     * @return BelongsToMany<Conversation, $this>
     */
    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot(['joined_at', 'last_read_at', 'is_admin'])
            ->withTimestamps();
    }

    /**
     * Messages sent by the user
     *
     * @return HasMany<Message, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Conversations created by the user
     *
     * @return HasMany<Conversation, $this>
     */
    public function createdConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'created_by');
    }

    /**
     * Get the avatar URL attribute.
     * Returns null if avatar_url is a local file path instead of a valid URL.
     */
    public function getAvatarUrlAttribute(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (! is_scalar($value)) {
            return null;
        }
        $stringValue = is_string($value) ? $value : (string) $value;

        // Check if it's a valid URL (starts with http:// or https://)
        if (filter_var($stringValue, FILTER_VALIDATE_URL)) {
            return $stringValue;
        }

        // Check if it's a relative path that should be made absolute
        if (str_starts_with($stringValue, '/') && ! str_starts_with($stringValue, '/private') && ! str_starts_with($stringValue, '/var')) {
            return url($stringValue);
        }

        // If it's a local file path (like /private/var/tmp/...), return null to use fallback
        return null;
    }

    /**
     * Users blocked by this user
     *
     * @return HasMany<BlockedUser, $this>
     */
    public function blockedUsers(): HasMany
    {
        return $this->hasMany(BlockedUser::class, 'blocker_id');
    }

    /**
     * Users who have blocked this user
     *
     * @return HasMany<BlockedUser, $this>
     */
    public function blockedBy(): HasMany
    {
        return $this->hasMany(BlockedUser::class, 'blocked_id');
    }

    /**
     * Check if this user has blocked another user
     */
    public function hasBlocked(self $user): bool
    {
        return $this->blockedUsers()->where('blocked_id', $user->id)->exists();
    }

    /**
     * Check if this user is blocked by another user
     */
    public function isBlockedBy(self $user): bool
    {
        return $this->blockedBy()->where('blocker_id', $user->id)->exists();
    }

    /**
     * Block a user
     */
    public function block(self $user): void
    {
        if (! $this->hasBlocked($user)) {
            $this->blockedUsers()->create(['blocked_id' => $user->id]);
        }
    }

    /**
     * Unblock a user
     */
    public function unblock(self $user): void
    {
        $this->blockedUsers()->where('blocked_id', $user->id)->delete();
    }

    /**
     * Check if viewer can see this user's profile
     */
    public function canBeViewedBy(?self $viewer): bool
    {
        if (! $viewer instanceof self) {
            return ($this->privacy_settings['profile_visibility'] ?? 'public') === 'public';
        }

        if ($viewer->id === $this->id) {
            return true;
        }

        if ($this->hasBlocked($viewer) || $this->isBlockedBy($viewer)) {
            return false;
        }

        $visibility = $this->privacy_settings['profile_visibility'] ?? 'public';

        return match ($visibility) {
            'public' => true,
            'followers' => $this->isFollowedBy($viewer),
            'private' => false,
            default => true,
        };
    }

    /**
     * Check if viewer can send messages to this user
     */
    public function canReceiveMessagesFrom(?self $sender): bool
    {
        if (! $sender instanceof self || $sender->id === $this->id) {
            return false;
        }

        if ($this->hasBlocked($sender) || $this->isBlockedBy($sender)) {
            return false;
        }

        $setting = $this->privacy_settings['who_can_message'] ?? 'everyone';

        return match ($setting) {
            'everyone' => true,
            'followers' => $this->isFollowedBy($sender),
            'none' => false,
            default => true,
        };
    }

    /**
     * Check if viewer can comment on this user's hunts
     */
    public function canReceiveCommentsFrom(?self $commenter): bool
    {
        if (! $commenter instanceof self) {
            return ($this->privacy_settings['who_can_comment'] ?? 'everyone') === 'everyone';
        }

        if ($commenter->id === $this->id) {
            return true;
        }

        if ($this->hasBlocked($commenter) || $this->isBlockedBy($commenter)) {
            return false;
        }

        $setting = $this->privacy_settings['who_can_comment'] ?? 'everyone';

        return match ($setting) {
            'everyone' => true,
            'followers' => $this->isFollowedBy($commenter),
            'disabled' => false,
            default => true,
        };
    }

    /**
     * Check if this user should appear in search results
     */
    public function isSearchable(): bool
    {
        return $this->privacy_settings['searchable'] ?? true;
    }

    /**
     * Check if this user's activity status should be shown
     */
    public function showsActivityStatus(): bool
    {
        return $this->privacy_settings['show_activity_status'] ?? true;
    }

    /**
     * Check if viewer can see this user's online status.
     */
    public function canShowOnlineStatusTo(?self $viewer): bool
    {

        if (! $this->showsActivityStatus()) {
            return false;
        }

        if ($viewer instanceof self && ! $viewer->showsActivityStatus()) {
            return false;
        }

        return ! ($viewer instanceof self && ($this->hasBlocked($viewer) || $this->isBlockedBy($viewer)));
    }

    /**
     * Check if this user is currently online.
     * Only returns true if the cache indicates online status.
     */
    public function isOnline(): bool
    {
        return cache()->has("user_online_{$this->id}");
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'datetime:d-m-Y',
            'skills' => 'array',
            'notification_settings' => 'array',
            'privacy_settings' => 'array',
            'onboarding_completed_at' => 'datetime',
        ];
    }
}
