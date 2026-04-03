<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * Export Service
 *
 * Handles CSV generation for trip data exports with proper formatting
 * and locale-aware column headers (Indonesian).
 */
class ExportService
{
    /**
     * Generate CSV content from trips collection.
     *
     * Creates CSV with columns: date, employee, duration, distance,
     * max_speed, avg_speed, violations, status. Uses Indonesian headers
     * for consistency with UI and proper number formatting for Excel.
     *
     * @param  Collection  $trips  Collection of Trip models with user relationship loaded
     * @return string CSV content as string
     */
    public function generateTripsCsv(Collection $trips): string
    {
        $csv = [];

        // Header row (Indonesian)
        $csv[] = [
            'Tanggal',
            'Karyawan',
            'Email',
            'Durasi',
            'Jarak (km)',
            'Kecepatan Maksimal (km/h)',
            'Kecepatan Rata-rata (km/h)',
            'Pelanggaran',
            'Status',
        ];

        // Data rows
        foreach ($trips as $trip) {
            $csv[] = [
                $trip->started_at->format('Y-m-d H:i:s'),
                $trip->user->name,
                $trip->user->email,
                $this->formatDuration($trip->duration_seconds),
                number_format($trip->total_distance ?? 0, 2, '.', ''),
                number_format($trip->max_speed ?? 0, 1, '.', ''),
                number_format($trip->average_speed ?? 0, 1, '.', ''),
                $trip->violation_count,
                $this->getStatusText($trip->status->value),
            ];
        }

        // Convert to CSV string
        return $this->arrayToCsv($csv);
    }

    /**
     * Convert 2D array to CSV string.
     *
     * Uses PHP's native fputcsv for proper CSV formatting including
     * quote escaping and delimiter handling.
     *
     * @param  array<int, array<int, mixed>>  $data  2D array of CSV rows
     * @return string CSV formatted string
     */
    private function arrayToCsv(array $data): string
    {
        $output = fopen('php://temp', 'r+');

        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Format duration seconds to HH:MM:SS.
     *
     * Converts seconds into human-readable duration format suitable
     * for spreadsheet analysis.
     *
     * @param  int|null  $seconds  Duration in seconds
     * @return string Formatted duration string (HH:MM:SS)
     */
    private function formatDuration(?int $seconds): string
    {
        if ($seconds === null) {
            return '00:00:00';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }

    /**
     * Get status text in Indonesian.
     *
     * Translates trip status enum values to Indonesian text for
     * user-friendly CSV exports.
     *
     * @param  string  $status  Trip status enum value
     * @return string Indonesian status text
     */
    private function getStatusText(string $status): string
    {
        return match ($status) {
            'in_progress' => 'Sedang Berjalan',
            'completed' => 'Selesai',
            'auto_stopped' => 'Berhenti Otomatis',
            default => $status,
        };
    }
}
