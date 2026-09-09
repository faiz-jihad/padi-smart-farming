<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreIrrigationTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        $isAdmin = ($user->role === UserRole::Admin->value || (method_exists($user, 'hasRole') && $user->hasRole('admin')));
        $isOfficer = ($user->role === UserRole::ExtensionOfficer->value || (method_exists($user, 'hasRole') && $user->hasRole('extension_officer')));

        return $isAdmin || $isOfficer;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:irrigation_types,name'],
            'code' => ['nullable', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', 'unique:irrigation_types,code'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tipe irigasi / sistem pengairan wajib diisi.',
            'name.unique' => 'Nama tipe irigasi sudah terdaftar di sistem.',
            'name.max' => 'Nama tipe irigasi maksimal 100 karakter.',
            'code.unique' => 'Kode tipe irigasi sudah digunakan.',
            'code.max' => 'Kode tipe irigasi maksimal 50 karakter.',
            'code.regex' => 'Kode tipe irigasi hanya boleh huruf kecil, angka, dan garis bawah (_).',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
        ];
    }
}
