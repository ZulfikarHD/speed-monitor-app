/**
 * SpeedoMontor Service Worker
 *
 * Progressive Web App service worker for offline support and intelligent caching.
 * Implements cache-first strategy for static assets and network-first for app shell.
 *
 * Caching Strategy:
 * - Static Assets (JS/CSS): Cache-First (30 days, 60 entries max)
 * - Fonts (Bunny CDN): Cache-First (90 days, 10 entries max)
 * - App Shell (HTML): Network-First (3s timeout, cache fallback)
 * - API Routes: Network-Only (IndexedDB handles offline data)
 *
 * Version: 1.0.0
 * Last Updated: 2026-04-04
 */

// Cache version - increment to force cache refresh
const CACHE_VERSION = 1;
const CACHE_PREFIX = 'SpeedoMontor';

// Cache names
const CACHE_NAMES = {
    static: `${CACHE_PREFIX}-static-v${CACHE_VERSION}`,
    fonts: `${CACHE_PREFIX}-fonts-v${CACHE_VERSION}`,
    appShell: `${CACHE_PREFIX}-app-shell-v${CACHE_VERSION}`,
    images: `${CACHE_PREFIX}-images-v${CACHE_VERSION}`,
};

// Resources to precache on install
const PRECACHE_URLS = [
    '/',
    '/offline.html',
    '/manifest.json',
];

// ==============================================================================
// INSTALL EVENT
// ==============================================================================

/**
 * Service Worker installation.
 * Precaches critical resources for offline functionality.
 */
self.addEventListener('install', (event) => {
    console.log('[SW] Installing service worker v' + CACHE_VERSION);

    event.waitUntil(
        caches.open(CACHE_NAMES.appShell)
            .then((cache) => {
                console.log('[SW] Precaching app shell resources');
                return cache.addAll(PRECACHE_URLS);
            })
            .then(() => {
                // Force immediate activation
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('[SW] Precaching failed:', error);
            })
    );
});

// ==============================================================================
// ACTIVATE EVENT
// ==============================================================================

/**
 * Service Worker activation.
 * Cleans up old caches and takes control of all clients.
 */
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating service worker v' + CACHE_VERSION);

    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                // Delete old caches that don't match current version
                return Promise.all(
                    cacheNames
                        .filter((cacheName) => {
                            return cacheName.startsWith(CACHE_PREFIX) &&
                                   !Object.values(CACHE_NAMES).includes(cacheName);
                        })
                        .map((cacheName) => {
                            console.log('[SW] Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        })
                );
            })
            .then(() => {
                // Take control of all clients immediately
                return self.clients.claim();
            })
    );
});

// ==============================================================================
// FETCH EVENT
// ==============================================================================

/**
 * Service Worker fetch handler.
 * Routes requests to appropriate caching strategies.
 */
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip cross-origin requests (except fonts)
    if (url.origin !== location.origin && !url.hostname.includes('bunny.net')) {
        return;
    }

    // Route request to appropriate strategy
    if (isApiRequest(url)) {
        // API: Network-Only (IndexedDB handles offline)
        event.respondWith(networkOnly(request));
    } else if (isStaticAsset(url, request)) {
        // Static Assets: Cache-First
        event.respondWith(cacheFirst(request, CACHE_NAMES.static, {
            maxAge: 30 * 24 * 60 * 60, // 30 days
            maxEntries: 60,
        }));
    } else if (isFontRequest(url)) {
        // Fonts: Cache-First (long TTL)
        event.respondWith(cacheFirst(request, CACHE_NAMES.fonts, {
            maxAge: 90 * 24 * 60 * 60, // 90 days
            maxEntries: 10,
        }));
    } else if (isImageRequest(request)) {
        // Images: Cache-First
        event.respondWith(cacheFirst(request, CACHE_NAMES.images, {
            maxAge: 7 * 24 * 60 * 60, // 7 days
            maxEntries: 50,
        }));
    } else if (isNavigationRequest(request)) {
        // Navigation: Network-First with offline fallback
        event.respondWith(networkFirst(request, CACHE_NAMES.appShell));
    }
});

// ==============================================================================
// MESSAGE EVENT
// ==============================================================================

/**
 * Message handler for communication with clients.
 * Handles SKIP_WAITING command for update flow.
 */
self.addEventListener('message', (event) => {
    console.log('[SW] Message received:', event.data);

    if (event.data && event.data.type === 'SKIP_WAITING') {
        console.log('[SW] Skipping waiting and activating new service worker');
        self.skipWaiting();
    }
});

// ==============================================================================
// CACHING STRATEGIES
// ==============================================================================

/**
 * Cache-First strategy.
 * Returns cached response if available, otherwise fetches from network.
 *
 * @param {Request} request - Fetch request
 * @param {string} cacheName - Cache name to use
 * @param {Object} options - Cache options (maxAge, maxEntries)
 * @returns {Promise<Response>} Response from cache or network
 */
async function cacheFirst(request, cacheName, options = {}) {
    try {
        // Try cache first
        const cachedResponse = await caches.match(request);

        if (cachedResponse) {
            console.log('[SW] Cache hit:', request.url);
            return cachedResponse;
        }

        // Cache miss - fetch from network
        console.log('[SW] Cache miss, fetching:', request.url);
        const networkResponse = await fetch(request);

        // Cache successful responses only
        if (networkResponse && networkResponse.status === 200) {
            const cache = await caches.open(cacheName);

            // Clone response before caching (response can only be used once)
            cache.put(request, networkResponse.clone());

            // Enforce cache limits if specified
            if (options.maxEntries) {
                await enforceCacheLimit(cacheName, options.maxEntries);
            }
        }

        return networkResponse;
    } catch (error) {
        console.error('[SW] Cache-first failed:', error);

        // Try to return cached response as fallback
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }

        throw error;
    }
}

/**
 * Network-First strategy.
 * Fetches from network with timeout, falls back to cache if network fails.
 *
 * @param {Request} request - Fetch request
 * @param {string} cacheName - Cache name to use
 * @param {number} timeout - Network timeout in milliseconds (default: 3000)
 * @returns {Promise<Response>} Response from network or cache
 */
async function networkFirst(request, cacheName, timeout = 3000) {
    try {
        // Try network with timeout
        const networkResponse = await fetchWithTimeout(request, timeout);

        // Cache successful navigation responses
        if (networkResponse && networkResponse.status === 200) {
            const cache = await caches.open(cacheName);
            cache.put(request, networkResponse.clone());
        }

        return networkResponse;
    } catch (error) {
        console.log('[SW] Network failed, trying cache:', error.message);

        // Fallback to cache
        const cachedResponse = await caches.match(request);

        if (cachedResponse) {
            console.log('[SW] Serving from cache');
            return cachedResponse;
        }

        // No cache available - serve offline page
        if (request.mode === 'navigate') {
            const offlinePage = await caches.match('/offline.html');
            if (offlinePage) {
                return offlinePage;
            }
        }

        // No fallback available
        throw error;
    }
}

/**
 * Network-Only strategy.
 * Always fetches from network, no caching.
 *
 * @param {Request} request - Fetch request
 * @returns {Promise<Response>} Response from network
 */
async function networkOnly(request) {
    try {
        return await fetch(request);
    } catch (error) {
        console.error('[SW] Network-only request failed:', error);
        throw error;
    }
}

// ==============================================================================
// HELPER FUNCTIONS
// ==============================================================================

/**
 * Check if request is for API endpoint.
 *
 * @param {URL} url - Request URL
 * @returns {boolean} True if API request
 */
function isApiRequest(url) {
    return url.pathname.startsWith('/api/');
}

/**
 * Check if request is for static asset (JS/CSS from Vite build).
 *
 * @param {URL} url - Request URL
 * @param {Request} request - Fetch request
 * @returns {boolean} True if static asset
 */
function isStaticAsset(url, request) {
    return url.pathname.startsWith('/build/assets/') &&
           (request.destination === 'script' ||
            request.destination === 'style' ||
            url.pathname.endsWith('.js') ||
            url.pathname.endsWith('.css'));
}

/**
 * Check if request is for font file.
 *
 * @param {URL} url - Request URL
 * @returns {boolean} True if font request
 */
function isFontRequest(url) {
    return url.hostname.includes('bunny.net') ||
           url.pathname.match(/\.(woff|woff2|ttf|eot)$/);
}

/**
 * Check if request is for image.
 *
 * @param {Request} request - Fetch request
 * @returns {boolean} True if image request
 */
function isImageRequest(request) {
    return request.destination === 'image';
}

/**
 * Check if request is for navigation (HTML page).
 *
 * @param {Request} request - Fetch request
 * @returns {boolean} True if navigation request
 */
function isNavigationRequest(request) {
    return request.mode === 'navigate';
}

/**
 * Fetch with timeout.
 * Returns promise that rejects if fetch takes longer than timeout.
 *
 * @param {Request} request - Fetch request
 * @param {number} timeout - Timeout in milliseconds
 * @returns {Promise<Response>} Fetch response or timeout error
 */
function fetchWithTimeout(request, timeout) {
    return Promise.race([
        fetch(request),
        new Promise((_, reject) => {
            setTimeout(() => reject(new Error('Network timeout')), timeout);
        })
    ]);
}

/**
 * Enforce cache entry limit.
 * Removes oldest entries if cache exceeds maxEntries.
 *
 * @param {string} cacheName - Cache name
 * @param {number} maxEntries - Maximum number of entries
 */
async function enforceCacheLimit(cacheName, maxEntries) {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();

    // Remove oldest entries if over limit
    if (keys.length > maxEntries) {
        const entriesToDelete = keys.length - maxEntries;
        console.log(`[SW] Cache limit exceeded, deleting ${entriesToDelete} oldest entries`);

        for (let i = 0; i < entriesToDelete; i++) {
            await cache.delete(keys[i]);
        }
    }
}

// ==============================================================================
// LOGGING
// ==============================================================================

console.log('[SW] Service worker loaded - SpeedoMontor v' + CACHE_VERSION);
