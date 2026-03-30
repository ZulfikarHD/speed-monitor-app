<?php

namespace App\Models;

use App\Services\SpeedLogService;
use Database\Factories\SpeedLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

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
     * @var string|null
     */
    public const UPDATED_AT = null;

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

    /**
     * Bulk create speed logs for a trip.
     *
     * Facade method to SpeedLogService for convenient bulk insertion.
     *
     * @param  Trip  $trip  The trip to add speed logs to
     * @param  array<int, array{speed: float, recorded_at: string}>  $speedLogData  Array of speed log records
     * @return Collection<int, SpeedLog> Collection of created speed logs
     */
    public static function bulkCreate(Trip $trip, array $speedLogData): Collection
    {
        $service = new SpeedLogService;

        return $service->bulkInsert($trip, $speedLogData);
    }

    /**
     * Scope to filter only speed logs that are violations.
     *
     * @param  Builder<SpeedLog>  $query
     * @return Builder<SpeedLog>
     */
    public function scopeViolations(Builder $query): Builder
    {
        return $query->where('is_violation', true);
    }

    /**
     * Scope to filter only safe speed logs (non-violations).
     *
     * @param  Builder<SpeedLog>  $query
     * @return Builder<SpeedLog>
     */
    public function scopeSafe(Builder $query): Builder
    {
        return $query->where('is_violation', false);
    }
}
