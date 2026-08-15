<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in(['farmer', 'ppl', 'partner', 'admin'])],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'verification_status' => ['required', Rule::in(['pending', 'verified', 'rejected'])],
        ];
    }
}
