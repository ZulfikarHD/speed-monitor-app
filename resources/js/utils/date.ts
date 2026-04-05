/**
 * Date Formatting Utilities
 *
 * Provides consistent date, time, and duration formatting utilities
 * for the SpeedoMontor application, using Asia/Jakarta timezone.
 */

/**
 * Format a date string to a readable format.
 *
 * @param date - ISO 8601 date string
 * @returns Formatted date string (e.g., "2 April 2026")
 *
 * @example
 * ```ts
 * formatDate('2026-04-02T10:30:00Z'); // "2 April 2026"
 * ```
 */
export function formatDate(date: string): string {
    const d = new Date(date);

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        timeZone: 'Asia/Jakarta',
    }).format(d);
}

/**
 * Format a date string to a short date format.
 *
 * @param date - ISO 8601 date string
 * @returns Formatted short date string (e.g., "02/04/2026")
 *
 * @example
 * ```ts
 * formatShortDate('2026-04-02T10:30:00Z'); // "02/04/2026"
 * ```
 */
export function formatShortDate(date: string): string {
    const d = new Date(date);

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        timeZone: 'Asia/Jakarta',
    }).format(d);
}

/**
 * Format a date string to time only (HH:MM).
 *
 * @param date - ISO 8601 date string
 * @returns Formatted time string (e.g., "14:30")
 *
 * @example
 * ```ts
 * formatTime('2026-04-02T14:30:00Z'); // "14:30"
 * ```
 */
export function formatTime(date: string): string {
    const d = new Date(date);

    return new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
        timeZone: 'Asia/Jakarta',
    }).format(d);
}

/**
 * Format a date string to date and time.
 *
 * @param date - ISO 8601 date string
 * @returns Formatted date and time string (e.g., "2 April 2026, 14:30")
 *
 * @example
 * ```ts
 * formatDateTime('2026-04-02T14:30:00Z'); // "2 April 2026, 14:30"
 * ```
 */
export function formatDateTime(date: string): string {
    const d = new Date(date);

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
        timeZone: 'Asia/Jakarta',
    }).format(d);
}

/**
 * Format duration in seconds to HH:MM:SS format.
 *
 * @param seconds - Duration in seconds
 * @returns Formatted duration string (e.g., "01:23:45" or "23:45" for < 1 hour)
 *
 * @example
 * ```ts
 * formatDuration(5025); // "01:23:45"
 * formatDuration(1425); // "23:45"
 * formatDuration(45); // "00:45"
 * ```
 */
export function formatDuration(seconds: number | null): string {
    if (seconds === null || seconds === 0) {
return '00:00';
}

    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;

    if (hours > 0) {
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
}

/**
 * Format duration in seconds to a human-readable string.
 *
 * @param seconds - Duration in seconds
 * @returns Human-readable duration (e.g., "1 jam 23 menit", "45 menit")
 *
 * @example
 * ```ts
 * formatDurationHuman(5025); // "1 jam 23 menit"
 * formatDurationHuman(1425); // "23 menit"
 * formatDurationHuman(45); // "45 detik"
 * ```
 */
export function formatDurationHuman(seconds: number | null): string {
    if (seconds === null || seconds === 0) {
return '0 detik';
}

    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;

    const parts: string[] = [];

    if (hours > 0) {
        parts.push(`${hours} jam`);
    }

    if (minutes > 0) {
        parts.push(`${minutes} menit`);
    }

    if (secs > 0 && hours === 0) {
        parts.push(`${secs} detik`);
    }

    return parts.join(' ') || '0 detik';
}

/**
 * Get relative time string (e.g., "2 hours ago", "yesterday").
 *
 * @param date - ISO 8601 date string
 * @returns Relative time string in Indonesian
 *
 * @example
 * ```ts
 * formatRelativeTime('2026-04-02T12:00:00Z'); // "2 jam yang lalu"
 * ```
 */
export function formatRelativeTime(date: string): string {
    const now = new Date();
    const d = new Date(date);
    const diffMs = now.getTime() - d.getTime();
    const diffSecs = Math.floor(diffMs / 1000);
    const diffMins = Math.floor(diffSecs / 60);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffSecs < 60) {
return 'baru saja';
}

    if (diffMins < 60) {
return `${diffMins} menit yang lalu`;
}

    if (diffHours < 24) {
return `${diffHours} jam yang lalu`;
}

    if (diffDays === 1) {
return 'kemarin';
}

    if (diffDays < 7) {
return `${diffDays} hari yang lalu`;
}

    return formatDate(date);
}

/**
 * Convert date string to YYYY-MM-DD format for input fields.
 *
 * @param date - ISO 8601 date string or Date object
 * @returns Date string in YYYY-MM-DD format
 *
 * @example
 * ```ts
 * toInputDate('2026-04-02T14:30:00Z'); // "2026-04-02"
 * ```
 */
export function toInputDate(date: string | Date): string {
    const d = typeof date === 'string' ? new Date(date) : date;

    return d.toISOString().split('T')[0];
}

/**
 * Get today's date in YYYY-MM-DD format.
 *
 * @returns Today's date string
 *
 * @example
 * ```ts
 * getTodayDate(); // "2026-04-02"
 * ```
 */
export function getTodayDate(): string {
    return toInputDate(new Date());
}

/**
 * Format timestamp for chart axis labels.
 *
 * Returns time in HH:MM:SS format for chart X-axis display. Uses 24-hour
 * format to maintain consistency across chart visualization.
 *
 * @param timestamp - ISO 8601 timestamp string
 * @returns Time string in HH:MM:SS format (24-hour)
 *
 * @example
 * ```ts
 * formatChartTime('2026-04-02T10:30:45Z'); // "10:30:45"
 * ```
 */
export function formatChartTime(timestamp: string): string {
    const date = new Date(timestamp);

    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
        timeZone: 'Asia/Jakarta',
    });
}
