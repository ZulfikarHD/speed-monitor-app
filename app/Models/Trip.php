<?php

namespace App\Models;

use App\Enums\TripStatus;
use Database\Factories\TripFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trip Model
 *
 * Represents a single trip session with speed tracking, statistics,
 * and violation monitoring for employee speed compliance.
 */
#[Fillable([
    'user_id',
    'started_at',
    'ended_at',
    'status',
    'total_distance',
    'max_speed',
    'average_speed',
    'violation_count',
    'duration_seconds',
    'notes',
    'synced_at',
])]
class Trip extends Model
{
    /** @use HasFactory<TripFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'synced_at' => 'datetime',
            'status' => TripStatus::class,
            'total_distance' => 'decimal:2',
            'max_speed' => 'decimal:2',
            'average_speed' => 'decimal:2',
            'violation_count' => 'integer',
            'duration_seconds' => 'integer',
        ];
    }

    /**
     * Get the user who owns this trip.
     *
     * @return BelongsTo<User, Trip>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all speed logs for this trip.
     *
     * @return HasMany<SpeedLog, Trip>
     */
    public function speedLogs(): HasMany
    {
        return $this->hasMany(SpeedLog::class);
    }
}
