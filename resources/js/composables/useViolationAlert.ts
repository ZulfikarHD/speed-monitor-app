/**
 * Violation Alert Composable
 *
 * Provides multi-channel alerting for speed limit violations with browser
 * notifications, audio beeps, and visual flash triggers. Implements intelligent
 * cooldown logic to prevent alert spam while ensuring users are notified of
 * each distinct violation event.
 *
 * Alert Channels:
 * - Browser Notification API (with permission handling)
 * - Web Audio API (programmatic beep generation)
 * - Visual flash trigger (via callback to parent component)
 *
 * Features:
 * - Smart violation detection with 10-second cooldown
 * - Graceful fallback if notification permission denied
 * - Settings integration for user preference
 * - State management to prevent continuous alerts
 *
 * @example
 * ```ts
 * import { useViolationAlert } from '@/composables/useViolationAlert';
 *
 * const {
 *     checkViolation,
 *     triggerAlert,
 *     requestNotificationPermission,
 *     resetViolationState
 * } = useViolationAlert();
 *
 * // Request permission when trip starts
 * await requestNotificationPermission();
 *
 * // Check for violations in watch loop
 * watch([speed, speedLimit], ([currentSpeed, limit]) => {
 *     if (checkViolation(currentSpeed, limit)) {
 *         triggerAlert(currentSpeed, limit);
 *         gaugeRef.value?.triggerFlash();
 *     }
 * });
 * ```
 */

import { ref } from 'vue';

import { useSettingsStore } from '@/stores/settings';

// ========================================================================
// Type Definitions
// ========================================================================

/**
 * Browser notification permission state.
 */
type NotificationPermission = 'granted' | 'denied' | 'default';

/**
 * Violation alert composable return type.
 */
interface ViolationAlertComposable {
    notificationPermission: typeof notificationPermission;
    isViolating: typeof isViolating;
    lastViolationAt: typeof lastViolationAt;
    requestNotificationPermission: () => Promise<NotificationPermission>;
    checkViolation: (speed: number, speedLimit: number) => boolean;
    triggerAlert: (speed: number, speedLimit: number) => void;
    resetViolationState: () => void;
}

// ========================================================================
// State Management
// ========================================================================

/**
 * Current browser notification permission status.
 *
 * WHY: Track permission to avoid repeated requests and handle denied state.
 * WHY: Initialized to 'default' until actual permission checked.
 */
const notificationPermission = ref<NotificationPermission>('default');

/**
 * Whether currently exceeding speed limit (violation in progress).
 *
 * WHY: Track violation state to detect transition edges (enter/exit violation).
 * WHY: Prevents continuous alerts while remaining above limit.
 */
const isViolating = ref<boolean>(false);

/**
 * Timestamp of last alert trigger (milliseconds since epoch).
 *
 * WHY: Implement cooldown period to prevent alert spam.
 * WHY: Null when no alert has been triggered yet.
 */
const lastViolationAt = ref<number | null>(null);

/**
 * Web Audio API context for beep generation.
 *
 * WHY: Lazy initialization to avoid creating context until needed.
 * WHY: Reuse same context for all beeps to avoid resource leaks.
 */
let audioContext: AudioContext | null = null;

// ========================================================================
// Constants
// ========================================================================

/**
 * Minimum time between alerts in milliseconds (10 seconds).
 *
 * WHY: Prevent alert spam while still notifying distinct violations.
 * WHY: 10 seconds chosen as balance between effectiveness and annoyance.
 */
const COOLDOWN_PERIOD_MS = 10000;

/**
 * Beep sound duration in seconds.
 *
 * WHY: 300ms provides audible alert without being intrusive.
 */
const BEEP_DURATION_SEC = 0.3;

/**
 * Beep frequency in Hz.
 *
 * WHY: 800Hz chosen for attention-grabbing without being painful.
 * WHY: Falls within typical warning sound frequency range.
 */
const BEEP_FREQUENCY_HZ = 800;

/**
 * Beep volume (0.0 to 1.0).
 *
 * WHY: 0.3 volume is audible without being jarring.
 * WHY: Lower volume prevents startling driver during commute.
 */
const BEEP_VOLUME = 0.3;

// ========================================================================
// Notification Management
// ========================================================================

/**
 * Request browser notification permission from user.
 *
 * Checks if Notification API is available, then requests permission if not
 * already granted or denied. Updates permission state ref for UI feedback.
 *
 * WHY: Permission required before showing browser notifications.
 * WHY: Gracefully handles browsers without Notification API support.
 *
 * @returns Promise resolving to permission status ('granted' | 'denied' | 'default')
 *
 * @example
 * ```ts
 * const permission = await requestNotificationPermission();
 * if (permission === 'granted') {
 *     console.log('Notifications enabled');
 * }
 * ```
 */
async function requestNotificationPermission(): Promise<NotificationPermission> {
    // Check if Notification API is supported
    if (!('Notification' in window)) {
        console.warn('Browser does not support notifications');
        notificationPermission.value = 'denied';

        return 'denied';
    }

    // Check current permission status
    if (Notification.permission === 'granted') {
        notificationPermission.value = 'granted';

        return 'granted';
    }

    if (Notification.permission === 'denied') {
        notificationPermission.value = 'denied';

        return 'denied';
    }

    // Request permission (shows browser prompt)
    try {
        const permission = await Notification.requestPermission();
        notificationPermission.value = permission as NotificationPermission;

        return permission as NotificationPermission;
    } catch (error) {
        console.error('Error requesting notification permission:', error);
        notificationPermission.value = 'denied';

        return 'denied';
    }
}

/**
 * Show browser notification for speed violation.
 *
 * Creates and displays a notification with violation details. Only triggers
 * if permission is granted. Notification auto-closes after 5 seconds.
 *
 * WHY: Browser notification provides alert even if app not in foreground.
 * WHY: Indonesian language for local user base.
 *
 * @param speed - Current speed in km/h
 * @param speedLimit - Configured speed limit in km/h
 */
function showNotification(speed: number, speedLimit: number): void {
    if (notificationPermission.value !== 'granted') {
        return;
    }

    try {
        const notification = new Notification('⚠️ Peringatan Kecepatan', {
            body: `Anda melebihi batas kecepatan! (${Math.round(speed)} km/h / batas: ${speedLimit} km/h)`,
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            tag: 'speed-violation',
            requireInteraction: false,
            silent: false,
        });

        // Auto-close notification after 5 seconds
        setTimeout(() => {
            notification.close();
        }, 5000);
    } catch (error) {
        console.error('Error showing notification:', error);
    }
}

// ========================================================================
// Audio Alert
// ========================================================================

/**
 * Initialize Web Audio API context.
 *
 * Creates AudioContext instance on first use. Lazy initialization avoids
 * creating context until actually needed.
 *
 * WHY: Web Audio API requires user interaction before context can be created.
 * WHY: Reusing single context more efficient than creating new one each time.
 *
 * @returns AudioContext instance or null if not supported
 */
function initAudioContext(): AudioContext | null {
    if (audioContext) {
        return audioContext;
    }

    try {
        // Handle vendor prefixes (WebKit)
        const AudioContextClass =
            window.AudioContext ||
            (window as typeof window & { webkitAudioContext?: typeof AudioContext })
                .webkitAudioContext;

        if (!AudioContextClass) {
            console.warn('Web Audio API not supported');

            return null;
        }

        audioContext = new AudioContextClass();

        return audioContext;
    } catch (error) {
        console.error('Error initializing audio context:', error);

        return null;
    }
}

/**
 * Play attention-grabbing beep sound using Web Audio API.
 *
 * Generates 800Hz sine wave beep programmatically using oscillator and gain
 * nodes. Applies exponential fade-out to prevent clicking artifacts.
 *
 * WHY: Web Audio API allows beep generation without loading audio files.
 * WHY: Programmatic approach eliminates network request and file size.
 * WHY: Exponential ramp prevents audio clicking at end of beep.
 *
 * @example
 * ```ts
 * playBeep(); // Plays 300ms beep at 800Hz
 * ```
 */
function playBeep(): void {
    const context = initAudioContext();

    if (!context) {
        return;
    }

    try {
        // Create oscillator for tone generation
        const oscillator = context.createOscillator();
        const gainNode = context.createGain();

        // Connect audio graph: oscillator -> gain -> destination (speakers)
        oscillator.connect(gainNode);
        gainNode.connect(context.destination);

        // Configure oscillator
        oscillator.frequency.value = BEEP_FREQUENCY_HZ;
        oscillator.type = 'sine';

        // Configure volume envelope with fade-out to prevent clicking
        gainNode.gain.setValueAtTime(BEEP_VOLUME, context.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(
            0.01,
            context.currentTime + BEEP_DURATION_SEC,
        );

        // Schedule playback
        oscillator.start(context.currentTime);
        oscillator.stop(context.currentTime + BEEP_DURATION_SEC);
    } catch (error) {
        console.error('Error playing beep:', error);
    }
}

// ========================================================================
// Violation Detection Logic
// ========================================================================

/**
 * Check if speed exceeds limit and alert should be triggered.
 *
 * Implements intelligent violation detection with state tracking and cooldown
 * period. Only returns true when:
 * 1. Speed exceeds limit
 * 2. Not already violating (transition into violation state)
 * 3. Cooldown period has elapsed since last alert
 *
 * WHY: Prevent continuous alerts while remaining above limit.
 * WHY: Cooldown prevents alert spam during brief speed fluctuations.
 * WHY: State tracking detects violation boundaries (enter/exit).
 *
 * @param currentSpeed - Current speed in km/h
 * @param speedLimit - Configured speed limit in km/h
 * @returns True if alert should be triggered, false otherwise
 *
 * @example
 * ```ts
 * // Speed increases from 55 to 65 km/h (limit: 60)
 * checkViolation(65, 60); // Returns true (first violation)
 * checkViolation(70, 60); // Returns false (still violating, within cooldown)
 *
 * // Speed drops to 55 km/h, then increases to 65 km/h after 11 seconds
 * checkViolation(65, 60); // Returns true (new violation after cooldown)
 * ```
 */
function checkViolation(currentSpeed: number, speedLimit: number): boolean {
    const isExceeding = currentSpeed > speedLimit;
    const now = Date.now();

    // Check if this is a new violation (transition from OK to exceeding)
    if (isExceeding && !isViolating.value) {
        // Check cooldown period
        if (
            !lastViolationAt.value ||
            now - lastViolationAt.value > COOLDOWN_PERIOD_MS
        ) {
            // Update state
            isViolating.value = true;
            lastViolationAt.value = now;

            return true; // Trigger alert
        }
    }

    // Update violation state (allows exit from violation)
    isViolating.value = isExceeding;

    return false; // Don't trigger alert
}

/**
 * Trigger all alert channels (notification, audio, visual).
 *
 * Fires browser notification (if permitted), plays beep sound, and logs alert
 * event. Visual flash should be triggered by caller using component ref.
 *
 * WHY: Multi-channel approach ensures user notices violation.
 * WHY: Redundancy provides fallback if one channel fails or denied.
 *
 * @param speed - Current speed in km/h
 * @param speedLimit - Configured speed limit in km/h
 *
 * @example
 * ```ts
 * if (checkViolation(speed, limit)) {
 *     triggerAlert(speed, limit);
 *     gaugeRef.value?.triggerFlash(); // Visual flash handled separately
 * }
 * ```
 */
function triggerAlert(speed: number, speedLimit: number): void {
    // Check if alerts are enabled in settings
    const settingsStore = useSettingsStore();

    if (!settingsStore.settings.violation_alerts_enabled) {
        return;
    }

    // Trigger all alert channels
    showNotification(speed, speedLimit);
    playBeep();
}

/**
 * Reset violation state to initial values.
 *
 * Clears violation tracking when trip ends or alerts are disabled. Ensures
 * clean state for next trip session.
 *
 * WHY: Prevent stale state from affecting next trip.
 * WHY: Reset cooldown timer for fresh violation detection.
 *
 * @example
 * ```ts
 * // Reset when trip ends
 * watch(() => tripStore.hasActiveTrip, (isActive) => {
 *     if (!isActive) {
 *         resetViolationState();
 *     }
 * });
 * ```
 */
function resetViolationState(): void {
    isViolating.value = false;
    lastViolationAt.value = null;
}

// ========================================================================
// Composable Export
// ========================================================================

/**
 * Violation alert composable.
 *
 * Provides all functionality for speed violation alerting including notification
 * management, audio playback, and violation state tracking.
 *
 * @returns Object containing alert functions and reactive state
 */
export function useViolationAlert(): ViolationAlertComposable {
    return {
        // Reactive state (for UI binding)
        notificationPermission,
        isViolating,
        lastViolationAt,

        // Actions
        requestNotificationPermission,
        checkViolation,
        triggerAlert,
        resetViolationState,
    };
}
