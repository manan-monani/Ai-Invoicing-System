<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'rate' => $this->rate,
            'type' => $this->type,
            'is_active' => $this->is_active,
            'categories_count' => $this->whenCounted('categories'),
            'items_count' => $this->whenCounted('items'),
            'created_at' => $this->created_at,
        ];
    }
}
