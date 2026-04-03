<?php

namespace App\Http\Requests\Trip;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Export Trips Request Validation
 *
 * Validates query parameters for CSV export including filters for
 * employee, date range, status, and violations.
 */
class ExportTripsRequest extends FormRequest
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
     * Get the validation rules for exporting trips.
     *
     * Enforces valid date ranges, status values, and employee filters
     * to prevent invalid queries and ensure consistent export results.
     *
     * @return array<string, ValidationRule|array<mixed>|string> Validation rules
     */
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', 'string', 'in:in_progress,completed,auto_stopped'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'violations_only' => ['nullable', 'boolean'],
        ];
    }
}
