/**
 * Distance Calculation Utilities
 *
 * Provides accurate GPS-based distance calculation using the Haversine formula.
 * Used for trip distance tracking in the speedometer.
 */

/**
 * Calculate distance between two GPS coordinates using Haversine formula.
 *
 * The Haversine formula calculates the great-circle distance between two points
 * on a sphere given their longitudes and latitudes. This is the shortest distance
 * over the earth's surface.
 *
 * @param lat1 - First point latitude in degrees
 * @param lon1 - First point longitude in degrees
 * @param lat2 - Second point latitude in degrees
 * @param lon2 - Second point longitude in degrees
 * @returns Distance in meters
 *
 * @example
 * ```ts
 * const distance = haversineDistance(
 *   -6.2088, 106.8456, // Jakarta
 *   -6.9175, 107.6191  // Bandung
 * );
 * console.log(distance / 1000); // ~120 km
 * ```
 */
export function haversineDistance(
    lat1: number,
    lon1: number,
    lat2: number,
    lon2: number,
): number {
    const R = 6371000; // Earth radius in meters

    const dLat = ((lat2 - lat1) * Math.PI) / 180;
    const dLon = ((lon2 - lon1) * Math.PI) / 180;

    const a =
        Math.sin(dLat / 2) ** 2 +
        Math.cos((lat1 * Math.PI) / 180) *
            Math.cos((lat2 * Math.PI) / 180) *
            Math.sin(dLon / 2) ** 2;

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    return R * c;
}

/**
 * Convert meters to kilometers.
 */
export function metersToKm(meters: number): number {
    return meters / 1000;
}

/**
 * Convert meters to miles.
 */
export function metersToMiles(meters: number): number {
    return meters / 1609.34;
}
