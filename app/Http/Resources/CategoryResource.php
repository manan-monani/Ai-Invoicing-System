<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'tax_id' => $this->tax_id,
            'tax' => $this->whenLoaded('tax', fn () => new TaxResource($this->tax)),
            'is_active' => $this->is_active,
            'has_discount' => $this->has_discount,
            'discount_amount' => $this->discount_amount,
            'discount_is_percentage' => $this->discount_is_percentage,
            'items_count' => $this->whenCounted('items'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
