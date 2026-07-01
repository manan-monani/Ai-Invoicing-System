<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends BaseModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'sku',
        'description',
        'unit',
        'category_id',
        'tax_id',
        'unit_price',
        'cost_price',
        'has_discount',
        'discount_amount',
        'discount_is_percentage',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'has_discount' => 'boolean',
            'discount_amount' => 'decimal:2',
            'discount_is_percentage' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }
}
