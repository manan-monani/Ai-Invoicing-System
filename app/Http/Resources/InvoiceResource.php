<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = $this->status;
        if ((float) $this->balance <= 0) {
            $status = 'paid';
        } elseif ($this->due_date && $this->due_date->isPast()) {
            $status = 'overdue';
        } else {
            $status = $status === 'sent' ? 'sent' : 'draft';
        }

        return [
            'id' => $this->id,
            'readable_id' => str_pad((string) $this->id, 6, '0', STR_PAD_LEFT),
            'customer_id' => $this->customer_id,
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
            ]),
            'issue_date' => $this->issue_date,
            'due_date' => $this->due_date,
            'status' => $status,
            'notes' => $this->notes,
            'discount' => $this->discount,
            'subtotal' => $this->subtotal,
            'tax_total' => $this->tax_total,
            'total' => $this->total,
            'balance' => $this->balance,
            'line_items' => InvoiceLineItemResource::collection($this->whenLoaded('lineItems')),
            'payments' => InvoicePaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
