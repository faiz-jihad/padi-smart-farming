<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class FaceLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'pin' => ['nullable', 'digits_between:4,6'],
            'face_descriptor' => ['required', 'array', 'size:128'],
            'face_descriptor.*' => ['numeric', 'between:-2,2'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
