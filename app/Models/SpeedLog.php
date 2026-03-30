<?php

namespace App\Models;

use Database\Factories\SpeedLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Speed Log Model
 *
 * Represents a single speed measurement recorded during a trip.
 * Logged every 5 seconds during active trip tracking.
 */
#[Fillable([
    'trip_id',
    'speed',
    'recorded_at',
    'is_violation',
])]
class SpeedLog extends Model
{
    /** @use HasFactory<SpeedLogFactory> */
    use HasFactory;

    /**
     * Disable updated_at timestamp (only uses created_at).
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'speed' => 'decimal:2',
            'recorded_at' => 'datetime',
            'is_violation' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the trip this speed log belongs to.
     *
     * @return BelongsTo<Trip, SpeedLog>
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}
