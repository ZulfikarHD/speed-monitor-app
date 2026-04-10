<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * List Users Request Validation
 *
 * Validates query parameters for filtering and searching users list
 * in the employee management page.
 */
class ListUsersRequest extends FormRequest
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
     * Get the validation rules for list users query parameters.
     *
     * Validates search term, role filter, and status filter to ensure
     * safe query execution and prevent SQL injection.
     *
     * @return array<string, ValidationRule|array<mixed>|string> Validation rules
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'in:employee,superuser,admin'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
