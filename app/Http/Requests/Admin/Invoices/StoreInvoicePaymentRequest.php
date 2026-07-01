<?php

namespace App\Http\Requests\Admin\Invoices;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoicePaymentRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'gt:0'],
            'paid_at' => ['required', 'date'],
            'payment_method_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('payment_methods', 'id')->where('is_active', true)],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Payment amount is required.',
            'amount.numeric' => 'Payment amount must be a valid number.',
            'amount.gt' => 'Payment amount must be greater than 0.',
            'paid_at.required' => 'Payment date is required.',
            'paid_at.date' => 'Payment date must be a valid date.',
            'payment_method_id.required' => 'Payment method is required.',
            'payment_method_id.integer' => 'Payment method is invalid.',
            'payment_method_id.exists' => 'Selected payment method is not available.',
        ];
    }
}
