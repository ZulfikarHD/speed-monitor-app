/**
 * Unit Conversion Utilities
 *
 * Provides speed unit conversion between m/s, km/h, and mph.
 */

export type SpeedUnit = 'kmh' | 'mph';

/**
 * Convert m/s to km/h.
 */
export function mpsToKmh(mps: number): number {
    return mps * 3.6;
}

/**
 * Convert m/s to mph.
 */
export function mpsToMph(mps: number): number {
    return mps * 2.23694;
}

/**
 * Convert km/h to m/s.
 */
export function kmhToMps(kmh: number): number {
    return kmh / 3.6;
}

/**
 * Convert mph to m/s.
 */
export function mphToMps(mph: number): number {
    return mph / 2.23694;
}

/**
 * Convert m/s to display speed based on unit preference.
 */
export function mpsToDisplay(mps: number, unit: SpeedUnit): number {
    return unit === 'kmh' ? mpsToKmh(mps) : mpsToMph(mps);
}

/**
 * Convert display speed to m/s based on unit preference.
 */
export function displayToMps(speed: number, unit: SpeedUnit): number {
    return unit === 'kmh' ? kmhToMps(speed) : mphToMps(speed);
}

/**
 * Get unit label string.
 */
export function getUnitLabel(unit: SpeedUnit): string {
    return unit === 'kmh' ? 'km/h' : 'mph';
}

/**
 * Estimate satellite count based on GPS accuracy.
 * Better accuracy generally means more satellites.
 */
export function estimateSatelliteCount(accuracy: number | null): number {
    if (accuracy === null) return 0;
    
    // Good accuracy (< 10m) = more satellites
    // Poor accuracy (> 50m) = fewer satellites
    const baseCount = 4;
    const bonusSats = Math.floor(100 / Math.max(1, accuracy));
    
    return Math.min(16, Math.max(0, baseCount + bonusSats));
}
