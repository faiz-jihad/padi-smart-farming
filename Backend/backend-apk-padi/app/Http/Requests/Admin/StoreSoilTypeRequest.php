<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreSoilTypeRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100', 'unique:soil_types,name'],
            'code' => ['nullable', 'string', 'max:50', 'unique:soil_types,code'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama jenis/tekstur tanah wajib diisi.',
            'name.unique' => 'Nama jenis tanah sudah terdaftar di sistem.',
            'name.max' => 'Nama jenis tanah maksimal 100 karakter.',
            'code.unique' => 'Kode jenis tanah sudah digunakan.',
            'code.max' => 'Kode jenis tanah maksimal 50 karakter.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
        ];
    }
}
