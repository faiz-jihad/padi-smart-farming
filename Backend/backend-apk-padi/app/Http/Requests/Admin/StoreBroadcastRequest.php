<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBroadcastRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:5000'],
            'type' => ['required', Rule::in(['info', 'warning', 'announcement', 'system'])],
            'status' => ['required', Rule::in(['draft', 'published', 'expired'])],
            'expires_at' => ['nullable', 'date'],
        ];
    }
}
