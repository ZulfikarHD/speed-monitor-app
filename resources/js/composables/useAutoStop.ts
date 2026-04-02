/**
 * Auto-Stop Composable
 *
 * Monitors trip inactivity (speed near zero) and automatically stops trip
 * after configured duration without movement. Prevents battery drain from
 * trips left running when vehicle is parked.
 *
 * Features:
 * - Monitors speed for movement detection
 * - Configurable inactivity threshold (default: 30 minutes)
 * - Configurable speed threshold (default: 5 km/h = considered stopped)
 * - Warning notifications before auto-stop
 * - Cancellable auto-stop if movement resumes
 */

import { onBeforeUnmount, ref, watch } from 'vue';

interface AutoStopOptions {
    /** Duration of inactivity before auto-stop (seconds) */
    inactivityDuration: number;
    /** Speed threshold - below this is considered stopped (km/h) */
    speedThreshold: number;
    /** Callback when auto-stop is about to trigger (5 min warning) */
    onWarning?: () => void;
    /** Callback when auto-stop triggers */
    onAutoStop: () => void;
}

interface UseAutoStopReturn {
    /** Start monitoring for inactivity */
    startMonitoring: (currentSpeed: () => number) => void;
    /** Stop monitoring */
    stopMonitoring: () => void;
    /** Reset inactivity timer */
    resetTimer: () => void;
    /** Seconds until auto-stop (0 if not inactive) */
    secondsUntilAutoStop: () => number;
    /** Whether currently monitoring */
    isMonitoring: () => boolean;
}

export function useAutoStop(options: AutoStopOptions): UseAutoStopReturn {
    let lastMovementTime: number | null = null;
    let checkInterval: ReturnType<typeof setInterval> | null = null;
    let speedGetter: (() => number) | null = null;
    const monitoring = ref<boolean>(false);

    /**
     * Start monitoring for inactivity.
     */
    function startMonitoring(getCurrentSpeed: () => number): void {
        if (monitoring.value) return;

        speedGetter = getCurrentSpeed;
        lastMovementTime = Date.now();
        monitoring.value = true;

        // Check every 10 seconds
        checkInterval = setInterval(() => {
            if (!speedGetter) return;

            const speed = speedGetter();

            // Movement detected - reset timer
            if (speed > options.speedThreshold) {
                lastMovementTime = Date.now();
                return;
            }

            // No movement - check inactivity duration
            if (lastMovementTime) {
                const inactiveSeconds = Math.floor(
                    (Date.now() - lastMovementTime) / 1000,
                );

                // Warning at 5 minutes before auto-stop
                const warningThreshold = options.inactivityDuration - 300;
                if (
                    inactiveSeconds >= warningThreshold &&
                    inactiveSeconds < warningThreshold + 10 &&
                    options.onWarning
                ) {
                    options.onWarning();
                }

                // Auto-stop triggered
                if (inactiveSeconds >= options.inactivityDuration) {
                    stopMonitoring();
                    options.onAutoStop();
                }
            }
        }, 10000); // Check every 10 seconds
    }

    /**
     * Stop monitoring for inactivity.
     */
    function stopMonitoring(): void {
        if (checkInterval) {
            clearInterval(checkInterval);
            checkInterval = null;
        }
        monitoring.value = false;
        lastMovementTime = null;
        speedGetter = null;
    }

    /**
     * Reset inactivity timer (called when movement resumes).
     */
    function resetTimer(): void {
        lastMovementTime = Date.now();
    }

    /**
     * Get seconds remaining until auto-stop.
     */
    function secondsUntilAutoStop(): number {
        if (!lastMovementTime || !monitoring.value) return 0;

        const inactiveSeconds = Math.floor(
            (Date.now() - lastMovementTime) / 1000,
        );

        if (inactiveSeconds >= options.inactivityDuration) return 0;

        return options.inactivityDuration - inactiveSeconds;
    }

    /**
     * Check if currently monitoring.
     */
    function isMonitoring(): boolean {
        return monitoring.value;
    }

    // Cleanup on unmount
    onBeforeUnmount(() => {
        stopMonitoring();
    });

    return {
        startMonitoring,
        stopMonitoring,
        resetTimer,
        secondsUntilAutoStop,
        isMonitoring,
    };
}
