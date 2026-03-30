<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Login Request Validation
 *
 * Validates user login credentials before authentication attempt.
 */
class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool Always true as login is public endpoint
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules for login credentials.
     *
     * Enforces email format and minimum password length to prevent
     * invalid authentication attempts and reduce server load.
     *
     * @return array<string, ValidationRule|array<mixed>|string> Validation rules
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
