<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class FaceEnrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pin' => ['nullable', 'confirmed', 'digits_between:4,6'],
            'face_descriptor' => ['nullable', 'required_without:face_descriptors', 'array', 'size:128'],
            'face_descriptor.*' => ['numeric', 'between:-2,2'],
            'face_descriptors' => ['nullable', 'required_without:face_descriptor', 'array', 'min:3', 'max:5'],
            'face_descriptors.*' => ['array', 'size:128'],
            'face_descriptors.*.*' => ['numeric', 'between:-2,2'],
        ];
    }
}
