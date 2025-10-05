<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a blocking relationship between two users.
 *
 * @property int $id
 * @property int $blocker_id
 * @property int $blocked_id
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 * @property-read User $blocker
 * @property-read User $blocked
 */
final class BlockedUser extends Model
{
    protected $fillable = [
        'blocker_id',
        'blocked_id',
    ];

    /**
     * Get the user who blocked.
     *
     * @return BelongsTo<User, $this>
     */
    public function blocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocker_id');
    }

    /**
     * Get the user who was blocked.
     *
     * @return BelongsTo<User, $this>
     */
    public function blocked(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_id');
    }
}
