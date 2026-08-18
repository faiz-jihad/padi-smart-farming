<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdatePublicProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('farmer')->check();
    }

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:150'],
            'headline'      => ['nullable', 'string', 'max:255'],
            'description'   => ['nullable', 'string', 'max:3000'],
            'logo'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'cover_image'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'whatsapp'      => ['nullable', 'string', 'max:30'],
            'public_email'  => ['nullable', 'email', 'max:150'],
            'public_phone'  => ['nullable', 'string', 'max:30'],
            'public_address'=> ['nullable', 'string', 'max:500'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'facebook_url'  => ['nullable', 'url', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'business_name.required' => 'Nama usaha wajib diisi.',
            'logo.image'             => 'Logo harus berupa gambar.',
            'logo.max'               => 'Ukuran logo maksimal 2MB.',
            'cover_image.image'      => 'Cover harus berupa gambar.',
            'cover_image.max'        => 'Ukuran cover maksimal 4MB.',
            'instagram_url.url'      => 'Link Instagram tidak valid.',
            'facebook_url.url'       => 'Link Facebook tidak valid.',
        ];
    }
}
