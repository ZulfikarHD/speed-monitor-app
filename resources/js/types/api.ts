/**
 * API Request and Response Type Definitions
 *
 * Defines TypeScript interfaces for authentication API endpoints.
 * These types ensure type safety when making API calls and handling responses.
 *
 * @example
 * ```ts
 * const credentials: LoginCredentials = {
 *   email: 'user@example.com',
 *   password: 'secret123'
 * };
 *
 * const response: LoginResponse = await api.login(credentials);
 * console.log(response.user.name, response.token);
 * ```
 */

/**
 * Login request credentials.
 *
 * User-provided credentials for authentication.
 */
export interface LoginCredentials {
    /** User email address */
    email: string;
    /** User password */
    password: string;
}

/**
 * Login response data.
 *
 * Returned by backend after successful authentication.
 * Contains user data and Sanctum API token.
 */
export interface LoginResponse {
    /** Authenticated user data */
    user: {
        /** Unique user identifier */
        id: number;
        /** Full name */
        name: string;
        /** Email address */
        email: string;
        /** User role for RBAC */
        role: 'employee' | 'superuser' | 'admin';
        /** Account status */
        is_active: boolean;
    };
    /** Sanctum API token for authenticated requests */
    token: string;
}

/**
 * API error response structure.
 *
 * Standard Laravel validation error response format.
 */
export interface ApiError {
    /** Error message */
    message: string;
    /** Field-specific validation errors (422 responses) */
    errors?: Record<string, string[]>;
}

/**
 * Generic API response wrapper.
 *
 * Standardizes API response structure across endpoints.
 *
 * @template T - The data type for successful responses
 */
export interface ApiResponse<T> {
    /** Response data on success */
    data?: T;
    /** Error information on failure */
    error?: ApiError;
    /** HTTP status code */
    status: number;
}
