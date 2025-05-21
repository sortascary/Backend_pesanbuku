<?php

namespace App\Http\Requests\V1\Order;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
            'phone' => 'nullable|string',
            'schoolName' => 'nullable|string',
            'daerah' => 'nullable|string',
            'payment' => 'required|string',
            'status' => 'required|string',
            'books' => 'required|array|min:1',
            'books.*.book_class_id' => 'required|exists:book_classes,id',
            'books.*.amount' => 'required|integer|min:1',
        ];
    }
}
