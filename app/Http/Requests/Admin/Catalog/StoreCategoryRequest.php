<?php

namespace App\Http\Requests\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'tax_id' => ['nullable', 'integer', 'exists:taxes,id'],
            'is_active' => ['sometimes', 'boolean'],
            'has_discount' => ['required', 'boolean'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_is_percentage' => ['sometimes', 'boolean'],
        ];
    }
}
