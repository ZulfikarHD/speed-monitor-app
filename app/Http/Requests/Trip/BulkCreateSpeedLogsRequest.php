<?php

namespace App\Http\Requests\Trip;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Bulk Create Speed Logs Request Validation
 *
 * Validates bulk speed log insertion for offline sync scenarios where
 * multiple speed logs are submitted at once. Ensures data integrity by
 * validating array structure and individual log entries.
 */
class BulkCreateSpeedLogsRequest extends FormRequest
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
     * Get the validation rules for bulk speed log creation.
     *
     * Validates the speed_logs array and each entry within it. Limits array size
     * to 1000 entries to prevent memory issues while supporting typical offline
     * sync batches (e.g., 30 min trip = 360 logs at 5-second intervals).
     *
     * @return array<string, ValidationRule|array<mixed>|string> Validation rules
     */
    public function rules(): array
    {
        return [
            'speed_logs' => ['required', 'array', 'min:1', 'max:1000'],
            'speed_logs.*.speed' => ['required', 'numeric', 'min:0'],
            'speed_logs.*.recorded_at' => ['required', 'date_format:Y-m-d H:i:s'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * Provides user-friendly error messages for validation failures to help
     * clients debug sync issues during offline data upload.
     *
     * @return array<string, string> Custom validation messages
     */
    public function messages(): array
    {
        return [
            'speed_logs.required' => 'The speed logs array is required',
            'speed_logs.array' => 'The speed logs must be an array',
            'speed_logs.min' => 'At least one speed log is required',
            'speed_logs.max' => 'Cannot insert more than 1000 speed logs at once',
            'speed_logs.*.speed.required' => 'Each speed log must have a speed value',
            'speed_logs.*.speed.numeric' => 'Speed must be a numeric value',
            'speed_logs.*.speed.min' => 'Speed cannot be negative',
            'speed_logs.*.recorded_at.required' => 'Each speed log must have a recorded_at timestamp',
            'speed_logs.*.recorded_at.date_format' => 'The recorded_at field must be in Y-m-d H:i:s format',
        ];
    }
}
