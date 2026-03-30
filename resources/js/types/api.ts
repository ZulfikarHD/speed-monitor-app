/**
 * API request and response type definitions.
 *
 * Defines TypeScript interfaces for API interactions including
 * authentication, error handling, and generic API responses.
 */

import type { User } from './auth';

/**
 * Login credentials for authentication request.
 */
export interface LoginCredentials {
    /** User email address */
    email: string;
    /** User password (min 8 characters) */
    password: string;
}

/**
 * Login API response with user data and token.
 */
export interface LoginResponse {
    /** Sanctum API token for authentication */
    token: string;
    /** Authenticated user object with role */
    user: User;
}

/**
 * API error response structure.
 */
export interface ApiError {
    /** Human-readable error message */
    message: string;
    /** Field-specific validation errors (optional) */
    errors?: Record<string, string[]>;
}

/**
 * Generic API response wrapper.
 *
 * @template T - Type of the response data
 */
export interface ApiResponse<T> {
    /** Response data (present on success) */
    data?: T;
    /** Error details (present on failure) */
    error?: ApiError;
    /** HTTP status code */
    status: number;
}
