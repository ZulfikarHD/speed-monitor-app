<?php

namespace App\Services;

use App\Models\Trip;
use Carbon\Carbon;

/**
 * Trip Analysis Service
 *
 * Analyzes trip patterns to detect suspicious behavior that indicates gaming.
 *
 * Suspicious patterns detected:
 * - Very short trips (< 5 min) - cherry-picking slow segments
 * - Too many trips in one day - splitting to hide speeding
 * - Very low distance (< 500m) - fake trips
 * - Unusual time patterns - stopping before speeding would occur
 */
class TripAnalysisService
{
    /**
     * Analyze trip when it ends and flag if suspicious.
     */
    public function analyzeTripOnEnd(Trip $trip): void
    {
        $reasons = [];

        // 1. Check trip duration (< 5 minutes is suspicious)
        if ($trip->duration_seconds && $trip->duration_seconds < 300) {
            $reasons[] = [
                'code' => 'SHORT_DURATION',
                'message' => 'Trip duration sangat singkat (< 5 menit)',
                'severity' => 'medium',
                'value' => $trip->duration_seconds,
            ];
        }

        // 2. Check distance (< 500m is suspicious)
        if ($trip->total_distance && $trip->total_distance < 500) {
            $reasons[] = [
                'code' => 'LOW_DISTANCE',
                'message' => 'Jarak tempuh sangat rendah (< 500m)',
                'severity' => 'medium',
                'value' => $trip->total_distance,
            ];
        }

        // 3. Check trip count today
        $tripsToday = Trip::where('user_id', $trip->user_id)
            ->whereDate('started_at', $trip->started_at)
            ->count();

        $trip->trip_count_today = $tripsToday;

        if ($tripsToday > 6) {
            $reasons[] = [
                'code' => 'EXCESSIVE_TRIPS',
                'message' => "Terlalu banyak trip dalam satu hari ({$tripsToday} trips)",
                'severity' => 'high',
                'value' => $tripsToday,
            ];
        }

        // 4. Check recent trip pattern (multiple trips in 2 hours)
        $recentTrips = Trip::where('user_id', $trip->user_id)
            ->where('started_at', '>=', Carbon::parse($trip->started_at)->subHours(2))
            ->where('started_at', '<=', $trip->started_at)
            ->count();

        if ($recentTrips > 3) {
            $reasons[] = [
                'code' => 'RAPID_TRIPS',
                'message' => "{$recentTrips} trip dalam 2 jam terakhir",
                'severity' => 'high',
                'value' => $recentTrips,
            ];
        }

        // 5. Check if distance/duration ratio is suspicious
        if ($trip->total_distance && $trip->duration_seconds) {
            // Average speed in m/s
            $avgSpeed = $trip->total_distance / $trip->duration_seconds;

            // If average speed < 1 m/s (3.6 km/h), likely parked/fake
            if ($avgSpeed < 1) {
                $reasons[] = [
                    'code' => 'SUSPICIOUSLY_SLOW',
                    'message' => 'Kecepatan rata-rata terlalu rendah (< 3.6 km/h)',
                    'severity' => 'medium',
                    'value' => round($avgSpeed * 3.6, 2),
                ];
            }
        }

        // 6. Check if trip has no speed logs (manual creation?)
        $speedLogCount = $trip->speedLogs()->count();
        if ($speedLogCount === 0) {
            $reasons[] = [
                'code' => 'NO_SPEED_LOGS',
                'message' => 'Tidak ada data speed log',
                'severity' => 'critical',
                'value' => 0,
            ];
        }

        // 7. Check if violations are suspiciously low for long trips
        if ($trip->duration_seconds > 1800) { // > 30 minutes
            // Long trips with 0 violations might be cherry-picked segments
            if ($trip->violation_count === 0) {
                $reasons[] = [
                    'code' => 'LONG_TRIP_NO_VIOLATIONS',
                    'message' => 'Trip lama (> 30 menit) tanpa violation - kemungkinan hanya segmen lambat',
                    'severity' => 'low',
                    'value' => $trip->duration_seconds,
                ];
            }
        }

        // Flag trip if any suspicious reasons found
        if (! empty($reasons)) {
            $trip->is_suspicious = true;
            $trip->suspicious_reasons = $reasons;
            $trip->flagged_at = now();
        }

        $trip->save();
    }

    /**
     * Get suspicious trips for superuser review.
     */
    public function getSuspiciousTrips(int $limit = 50)
    {
        return Trip::where('is_suspicious', true)
            ->with('user:id,name,email')
            ->orderBy('flagged_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($trip) {
                $highSeverityCount = collect($trip->suspicious_reasons ?? [])
                    ->where('severity', 'high')
                    ->count();

                $criticalSeverityCount = collect($trip->suspicious_reasons ?? [])
                    ->where('severity', 'critical')
                    ->count();

                return [
                    'trip' => $trip,
                    'severity_score' => $criticalSeverityCount * 3 + $highSeverityCount * 2 + count($trip->suspicious_reasons ?? []),
                    'critical_count' => $criticalSeverityCount,
                    'high_count' => $highSeverityCount,
                ];
            })
            ->sortByDesc('severity_score')
            ->values();
    }

    /**
     * Get suspicious stats per user.
     */
    public function getSuspiciousStatsByUser(int $userId, Carbon $startDate, Carbon $endDate)
    {
        $trips = Trip::where('user_id', $userId)
            ->whereBetween('started_at', [$startDate, $endDate])
            ->get();

        $suspiciousTrips = $trips->where('is_suspicious', true);

        $reasonCounts = [];
        foreach ($suspiciousTrips as $trip) {
            foreach ($trip->suspicious_reasons ?? [] as $reason) {
                $code = $reason['code'];
                if (! isset($reasonCounts[$code])) {
                    $reasonCounts[$code] = [
                        'code' => $code,
                        'message' => $reason['message'],
                        'count' => 0,
                    ];
                }
                $reasonCounts[$code]['count']++;
            }
        }

        return [
            'total_trips' => $trips->count(),
            'suspicious_trips' => $suspiciousTrips->count(),
            'suspicious_percentage' => $trips->count() > 0
                ? round(($suspiciousTrips->count() / $trips->count()) * 100, 2)
                : 0,
            'reason_breakdown' => array_values($reasonCounts),
            'risk_level' => $this->calculateRiskLevel($suspiciousTrips->count(), $trips->count()),
        ];
    }

    /**
     * Calculate risk level based on suspicious trip percentage.
     */
    private function calculateRiskLevel(int $suspiciousCount, int $totalCount): string
    {
        if ($totalCount === 0) {
            return 'unknown';
        }

        $percentage = ($suspiciousCount / $totalCount) * 100;

        if ($percentage > 50) {
            return 'critical'; // > 50% suspicious = high risk of gaming
        } elseif ($percentage > 30) {
            return 'high'; // > 30% suspicious = likely gaming
        } elseif ($percentage > 15) {
            return 'medium'; // > 15% suspicious = monitor closely
        } elseif ($percentage > 5) {
            return 'low'; // > 5% suspicious = normal variation
        }

        return 'normal'; // < 5% suspicious = acceptable
    }
}
