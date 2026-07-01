<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaxSettingsRequest extends FormRequest
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
            'tax_enabled' => ['required', 'boolean'],
            'tax_mode' => ['required', Rule::in(['none', 'global', 'item', 'category'])],
            'default_tax_id' => [
                'nullable',
                'exists:taxes,id',
                Rule::requiredIf(fn () => $this->boolean('tax_enabled') && $this->input('tax_mode') === 'global'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'tax_enabled.required' => 'Tax enabled is required.',
            'tax_enabled.boolean' => 'Tax enabled must be true or false.',
            'tax_mode.required' => 'Tax mode is required.',
            'tax_mode.in' => 'Tax mode must be one of: none, global, item, or category.',
            'default_tax_id.required_if' => 'Please select a default tax when global mode is enabled.',
            'default_tax_id.exists' => 'Selected default tax is invalid.',
        ];
    }
}
