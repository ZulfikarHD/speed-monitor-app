<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Store User Request Validation
 *
 * Validates user creation data including credentials, role assignment,
 * and account status for new user registration.
 */
class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool Authorization handled by UserPolicy
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules for creating a new user.
     *
     * Enforces unique email, password strength requirements,
     * valid role assignment, and account status validation.
     *
     * @return array<string, ValidationRule|array<mixed>|string> Validation rules
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'string', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', Password::defaults()],
            'role' => ['required', 'string', 'in:employee,supervisor,admin'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string> Custom error messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama karyawan wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role tidak valid.',
        ];
    }
}
