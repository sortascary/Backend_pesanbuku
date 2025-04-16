<?php

namespace App\Http\Requests\V1\User;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:sekolah,distributor',
            'FCMToken' => 'nullable|string|max:255',
            'daerah' => 'required_if:role,sekolah|nullable|string|max:255',
            'schoolName' => 'required_if:role,sekolah|nullable|string|max:255',
            'distributor_key' => 'required_if:role,distributor|string',
        ];
    }
}
