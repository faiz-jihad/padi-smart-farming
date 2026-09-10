<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Enums\UserRole;
use App\Helpers\PhoneNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower((string) $this->input('email')),
            'phone' => PhoneNormalizer::normalize($this->input('phone')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
            'pin' => ['nullable', 'confirmed', 'digits_between:4,6'],
            'face_descriptor' => ['nullable', 'array', 'size:128'],
            'face_descriptor.*' => ['numeric', 'between:-2,2'],
            'face_descriptors' => ['nullable', 'array', 'min:3', 'max:5'],
            'face_descriptors.*' => ['array', 'size:128'],
            'face_descriptors.*.*' => ['numeric', 'between:-2,2'],
            'account_type' => ['required', Rule::in(UserRole::publicValues())],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $hasPin = filled($this->input('pin'));
                $hasFace = filled($this->input('face_descriptor')) || filled($this->input('face_descriptors'));

                if ($hasPin && ! $hasFace) {
                    $validator->errors()->add('face_descriptor', 'Daftarkan wajah untuk mengaktifkan login wajah.');
                }

            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'account_type.in' => 'Jenis akun hanya boleh Petani atau Pembeli.',
            'email.unique' => 'Email sudah terdaftar.',
            'phone.unique' => 'Nomor telepon sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }
}
