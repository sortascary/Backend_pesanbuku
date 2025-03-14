<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users',
            'daerah' => 'nullable|string|max:255',
            'schoolName' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'FCMToken' => 'nullable|string|max:255',
            'password' => 'required|string|min:8',
        ];
    }
}
