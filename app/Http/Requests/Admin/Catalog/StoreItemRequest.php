<?php

namespace App\Http\Requests\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemRequest extends FormRequest
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
            'sku' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'unit' => ['required', 'string', Rule::in(['pcs', 'hour', 'kg'])],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'tax_id' => ['nullable', 'integer', 'exists:taxes,id'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'has_discount' => ['sometimes', 'boolean'],
            'discount_amount' => [
                Rule::requiredIf(fn () => $this->boolean('has_discount')),
                'numeric',
                'min:0',
            ],
            'discount_is_percentage' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
