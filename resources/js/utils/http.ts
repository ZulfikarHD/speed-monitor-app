/**
 * HTTP client utilities for API requests with Sanctum authentication.
 *
 * Provides a configured HTTP client that properly handles CSRF tokens
 * and credentials for Sanctum stateful authentication.
 */

interface RequestConfig extends RequestInit {
    params?: Record<string, any>;
}

interface HttpClient {
    get<T = any>(url: string, config?: RequestConfig): Promise<T>;
    post<T = any>(url: string, data?: any, config?: RequestConfig): Promise<T>;
    put<T = any>(url: string, data?: any, config?: RequestConfig): Promise<T>;
    delete<T = any>(url: string, config?: RequestConfig): Promise<T>;
}

/**
 * Get CSRF token from meta tag or cookie.
 */
function getCsrfToken(): string | null {
    // Try meta tag first (set by Laravel)
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (token) {
        return token;
    }

    // Fallback to cookie
    const cookies = document.cookie.split(';');

    for (const cookie of cookies) {
        const [name, value] = cookie.trim().split('=');

        if (name === 'XSRF-TOKEN') {
            return decodeURIComponent(value);
        }
    }

    return null;
}

/**
 * Build URL with query parameters.
 */
function buildUrl(url: string, params?: Record<string, any>): string {
    if (!params || Object.keys(params).length === 0) {
        return url;
    }

    const searchParams = new URLSearchParams();

    for (const [key, value] of Object.entries(params)) {
        if (value !== undefined && value !== null) {
            searchParams.append(key, String(value));
        }
    }

    const separator = url.includes('?') ? '&' : '?';

    return `${url}${separator}${searchParams.toString()}`;
}

/**
 * Make HTTP request with proper Sanctum authentication.
 */
async function request<T>(
    method: string,
    url: string,
    data?: any,
    config?: RequestConfig
): Promise<T> {
    const csrfToken = getCsrfToken();
    
    const headers: HeadersInit = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...config?.headers,
    };

    // Add CSRF token if available
    if (csrfToken) {
        headers['X-XSRF-TOKEN'] = csrfToken;
    }

    // Build final URL with query params
    const finalUrl = buildUrl(url, config?.params);

    const response = await fetch(finalUrl, {
        method,
        headers,
        credentials: 'same-origin', // Include cookies for Sanctum
        body: data ? JSON.stringify(data) : undefined,
        ...config,
    });

    // Handle non-2xx responses
    if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));

        throw new Error(
            errorData.message || `Request failed with status ${response.status}`
        );
    }

    // Handle 204 No Content
    if (response.status === 204) {
        return {} as T;
    }

    return response.json();
}

/**
 * HTTP client for API requests with Sanctum authentication.
 *
 * Automatically includes CSRF tokens and credentials for stateful authentication.
 *
 * @example
 * ```ts
 * import { http } from '@/utils/http';
 *
 * // GET request
 * const trips = await http.get('/api/trips');
 *
 * // POST request
 * const trip = await http.post('/api/trips', { notes: 'Morning commute' });
 *
 * // With query params
 * const trips = await http.get('/api/trips', { params: { page: 2 } });
 * ```
 */
export const http: HttpClient = {
    get: <T = any>(url: string, config?: RequestConfig) =>
        request<T>('GET', url, undefined, config),
    
    post: <T = any>(url: string, data?: any, config?: RequestConfig) =>
        request<T>('POST', url, data, config),
    
    put: <T = any>(url: string, data?: any, config?: RequestConfig) =>
        request<T>('PUT', url, data, config),
    
    delete: <T = any>(url: string, config?: RequestConfig) =>
        request<T>('DELETE', url, undefined, config),
};
