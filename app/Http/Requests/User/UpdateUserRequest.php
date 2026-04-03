<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Update User Request Validation
 *
 * Validates user update data including profile changes, role reassignment,
 * password updates, and account status modifications.
 */
class UpdateUserRequest extends FormRequest
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
     * Get the validation rules for updating an existing user.
     *
     * Validates name, email uniqueness (excluding current user),
     * optional password change, role assignment, and account status.
     *
     * @return array<string, ValidationRule|array<mixed>|string> Validation rules
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'string',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->route('user')),
            ],
            'password' => ['nullable', 'string', 'min:8', Password::defaults()],
            'role' => ['sometimes', 'required', 'string', 'in:employee,supervisor,admin'],
            'is_active' => ['sometimes', 'required', 'boolean'],
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
            'email.unique' => 'Email sudah digunakan oleh pengguna lain.',
            'password.min' => 'Password minimal 8 karakter.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role tidak valid.',
        ];
    }
}
