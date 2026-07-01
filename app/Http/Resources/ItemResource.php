<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
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
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'unit' => $this->unit,
            'category_id' => $this->category_id,
            'tax_id' => $this->tax_id,
            'unit_price' => $this->unit_price,
            'cost_price' => $this->cost_price,
            'has_discount' => $this->has_discount,
            'discount_amount' => $this->discount_amount,
            'discount_is_percentage' => $this->discount_is_percentage,
            'is_active' => $this->is_active,
            'category' => $this->whenLoaded('category', fn () => new CategoryResource($this->category)),
            'tax' => $this->whenLoaded('tax', fn () => new TaxResource($this->tax)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
