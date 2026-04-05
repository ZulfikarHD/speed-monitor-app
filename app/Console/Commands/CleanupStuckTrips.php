<?php

namespace App\Console\Commands;

use App\Enums\TripStatus;
use App\Models\Trip;
use Illuminate\Console\Command;

/**
 * Cleanup Stuck Trips Command
 *
 * Finds trips that have been in "in_progress" status for longer than a specified
 * duration and marks them as "auto_stopped". Useful for handling trips that were
 * never properly ended due to crashes, network issues, or bugs.
 */
class CleanupStuckTrips extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trips:cleanup-stuck
                            {--hours=24 : Hours after which a trip is considered stuck}
                            {--dry-run : Show what would be cleaned without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup trips stuck in "in_progress" status for too long';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $dryRun = $this->option('dry-run');

        $this->info("Looking for trips stuck in 'in_progress' status for more than {$hours} hours...");

        $threshold = now()->subHours($hours);

        $stuckTrips = Trip::where('status', TripStatus::InProgress)
            ->where('started_at', '<', $threshold)
            ->with('user:id,name,email')
            ->get();

        if ($stuckTrips->isEmpty()) {
            $this->info('✓ No stuck trips found!');
            return self::SUCCESS;
        }

        $this->warn("Found {$stuckTrips->count()} stuck trip(s):");
        $this->newLine();

        $table = [];
        foreach ($stuckTrips as $trip) {
            $table[] = [
                'ID' => $trip->id,
                'User' => $trip->user->name . ' (' . $trip->user->email . ')',
                'Started At' => $trip->started_at->format('Y-m-d H:i:s'),
                'Stuck For' => $trip->started_at->diffForHumans(),
            ];
        }

        $this->table(['ID', 'User', 'Started At', 'Stuck For'], $table);

        if ($dryRun) {
            $this->info('DRY RUN: No changes made. Remove --dry-run to actually cleanup these trips.');
            return self::SUCCESS;
        }

        if (! $this->confirm('Do you want to mark these trips as "auto_stopped"?', true)) {
            $this->info('Cancelled. No changes made.');
            return self::SUCCESS;
        }

        $cleaned = 0;
        foreach ($stuckTrips as $trip) {
            $trip->update([
                'status' => TripStatus::AutoStopped,
                'ended_at' => $trip->started_at->addHours($hours),
                'notes' => ($trip->notes ? $trip->notes . ' | ' : '') . 'Auto-stopped by cleanup command',
            ]);
            $cleaned++;
        }

        $this->info("✓ Successfully cleaned up {$cleaned} stuck trip(s)!");

        return self::SUCCESS;
    }
}
