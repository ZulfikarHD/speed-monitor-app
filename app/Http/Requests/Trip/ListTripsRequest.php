<?php

namespace App\Http\Requests\Trip;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * List Trips Request Validation
 *
 * Validates query parameters for trip listing including pagination,
 * filtering by user, status, and date range. Authorization for
 * user_id filtering is enforced in the controller.
 */
class ListTripsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True as authorization is handled by policy in controller
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules for listing trips.
     *
     * Enforces pagination limits, valid date ranges, status values,
     * sorting options, and violations filter to prevent performance
     * issues and invalid queries.
     *
     * @return array<string, ValidationRule|array<mixed>|string> Validation rules
     */
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', 'string', 'in:in_progress,completed,auto_stopped'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'sort_by' => ['nullable', 'string', 'in:started_at,violation_count,total_distance,duration_seconds'],
            'sort_order' => ['nullable', 'string', 'in:asc,desc'],
            'violations_only' => ['nullable', 'boolean'],
        ];
    }
}
