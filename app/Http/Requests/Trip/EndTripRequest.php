<?php

namespace App\Http\Requests\Trip;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * End Trip Request Validation
 *
 * Validates trip ending request allowing optional notes to be updated.
 * Authorization and status checks are handled by policy and controller.
 */
class EndTripRequest extends FormRequest
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
     * Get the validation rules for ending a trip.
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
