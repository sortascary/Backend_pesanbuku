<?php

namespace App\Http\Requests\V1\Book;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookRequest extends FormRequest
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
            'book_price' => 'required|array|min:1',
            'book_price.*.daerah' => 'required|string|max:255',
            'book_price.*.price' => 'required|integer|min:1000',
        ];
    }
}
