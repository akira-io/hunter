<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reshare extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hunt_id',
        'comment',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hunt(): BelongsTo
    {
        return $this->belongsTo(Hunt::class);
    }
}
