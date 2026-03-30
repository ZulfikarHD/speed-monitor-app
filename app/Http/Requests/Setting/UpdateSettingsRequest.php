<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Settings Request Validation
 *
 * Validates bulk settings update ensuring proper data types and ranges
 * for speed limit, auto-stop duration, and speed log interval settings.
 * Authorization is handled by policy in controller.
 */
class UpdateSettingsRequest extends FormRequest
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
     * Get the validation rules for updating settings.
     *
     * All fields are optional to allow partial updates. Speed limit must be
     * between 1-200 km/h. Auto-stop duration between 1 minute and 2 hours.
     * Speed log interval between 1-60 seconds for reasonable data granularity.
     *
     * @return array<string, ValidationRule|array<mixed>|string> Validation rules
     */
    public function rules(): array
    {
        return [
            'speed_limit' => ['nullable', 'numeric', 'min:1', 'max:200'],
            'auto_stop_duration' => ['nullable', 'integer', 'min:60', 'max:7200'],
            'speed_log_interval' => ['nullable', 'integer', 'min:1', 'max:60'],
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     *
     * @return array<string, string> Custom attribute names
     */
    public function attributes(): array
    {
        return [
            'speed_limit' => 'speed limit',
            'auto_stop_duration' => 'auto-stop duration',
            'speed_log_interval' => 'speed log interval',
        ];
    }
}
