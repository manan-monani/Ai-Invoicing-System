<?php

namespace App\Http\Requests\Admin\Invoices;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
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
            'customer_id' => [
                'nullable',
                Rule::exists('users', 'id')->where('type', 'client'),
                Rule::requiredIf(fn () => ! $this->filled('customer_email')),
            ],
            'customer_email' => [
                'nullable',
                'email',
                Rule::requiredIf(fn () => ! $this->filled('customer_id')),
            ],
            'customer_name' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(function () {
                    $email = $this->input('customer_email');
                    if (! $email) {
                        return false;
                    }

                    return ! User::query()->where('email', $email)->exists();
                }),
            ],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'status' => ['required', Rule::in(['draft', 'sent', 'paid', 'overdue'])],
            'notes' => ['nullable', 'string'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'line_items' => ['required', 'array', 'min:1'],
            'line_items.*.item_id' => ['nullable', 'exists:items,id', 'required_without:line_items.*.name'],
            'line_items.*.name' => ['nullable', 'string', 'max:255', 'required_without:line_items.*.item_id'],
            'line_items.*.description' => ['nullable', 'string'],
            'line_items.*.qty' => ['required', 'integer', 'min:1'],
            'line_items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'line_items.*.tax_id' => ['nullable', 'exists:taxes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer is required.',
            'customer_id.exists' => 'Selected customer is invalid.',
            'customer_email.required' => 'Customer email is required.',
            'customer_email.email' => 'Customer email must be valid.',
            'customer_name.required' => 'Customer name is required for new customers.',
            'issue_date.required' => 'Issue date is required.',
            'issue_date.date' => 'Issue date must be a valid date.',
            'due_date.required' => 'Due date is required.',
            'due_date.date' => 'Due date must be a valid date.',
            'due_date.after_or_equal' => 'Due date must be on or after the issue date.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be draft, sent, paid, or overdue.',
            'discount.numeric' => 'Discount must be a valid number.',
            'discount.min' => 'Discount must be zero or higher.',
            'line_items.required' => 'At least one line item is required.',
            'line_items.array' => 'Line items must be a list.',
            'line_items.min' => 'At least one line item is required.',
            'line_items.*.item_id.required_without' => 'Each line item must include an item or name.',
            'line_items.*.item_id.exists' => 'Selected item is invalid.',
            'line_items.*.name.required_without' => 'Each line item must include an item or name.',
            'line_items.*.name.max' => 'Item name may not be greater than 255 characters.',
            'line_items.*.qty.required' => 'Quantity is required.',
            'line_items.*.qty.integer' => 'Quantity must be a whole number.',
            'line_items.*.qty.min' => 'Quantity must be at least 1.',
            'line_items.*.unit_price.required' => 'Unit price is required.',
            'line_items.*.unit_price.min' => 'Unit price must be zero or higher.',
            'line_items.*.tax_id.exists' => 'Selected tax is invalid.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $lineItems = $this->input('line_items', []);
            if (! is_array($lineItems)) {
                return;
            }

            $seen = [];
            foreach ($lineItems as $index => $line) {
                $itemId = $line['item_id'] ?? null;
                if (! $itemId) {
                    continue;
                }

                $key = (string) $itemId;
                $seen[$key][] = $index;
            }

            foreach ($seen as $indexes) {
                if (count($indexes) < 2) {
                    continue;
                }
                foreach ($indexes as $index) {
                    $validator->errors()->add("line_items.{$index}.item_id", 'This item is already added.');
                }
            }
        });
    }
}
