<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceLineItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'item_id' => $this->item_id,
            'name' => $this->name ?? $this->item?->name,
            'description' => $this->description ?? $this->item?->description,
            'qty' => $this->qty,
            'unit_price' => $this->unit_price,
            'line_total' => $this->line_total,
            'tax_id' => $this->tax_id,
            'item' => $this->whenLoaded('item', fn () => [
                'id' => $this->item->id,
                'name' => $this->item->name,
                'unit' => $this->item->unit,
                'tax_id' => $this->item->tax_id,
                'category_id' => $this->item->category_id,
            ]),
            'tax' => $this->whenLoaded('tax', fn () => new TaxResource($this->tax)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
