<?php

namespace App\Http\Requests\V1\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'name' => 'nullable|string',
            'schoolName' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120' //max size is 5MB
        ];
    }
}
