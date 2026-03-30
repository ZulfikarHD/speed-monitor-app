<?php

namespace App\Http\Requests\Trip;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Start Trip Request Validation
 *
 * Validates trip creation request ensuring notes field meets
 * length requirements. Authentication and active trip checks
 * are handled in the controller.
 */
class StartTripRequest extends FormRequest
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
     * Get the validation rules for starting a trip.
     *
     * Notes field is optional but limited to 1000 characters to prevent
     * excessive data storage and maintain UI display consistency.
     *
     * @return array<string, ValidationRule|array<mixed>|string> Validation rules
     */
    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
