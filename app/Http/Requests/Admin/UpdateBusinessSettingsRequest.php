<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessSettingsRequest extends FormRequest
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
            'currency_symbol' => 'required|string|max:5',
            'currency_position' => 'required|string|in:left,right',
            'language' => 'required|string|in:en,ar',
            'invoice_prefix' => 'nullable|string|max:10',
            'invoice_terms' => 'nullable|string|max:5000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'currency_symbol.required' => 'Currency symbol is required.',
            'currency_symbol.max' => 'Currency symbol must not exceed 5 characters.',
            'currency_position.required' => 'Currency position is required.',
            'currency_position.in' => 'Currency position must be left or right.',
            'language.required' => 'Language is required.',
            'language.in' => 'Selected language is invalid.',
            'invoice_prefix.max' => 'Invoice prefix must not exceed 10 characters.',
            'invoice_terms.max' => 'Terms and conditions must not exceed 5000 characters.',
        ];
    }
}
