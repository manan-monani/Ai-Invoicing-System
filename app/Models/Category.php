<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends BaseModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'tax_id',
        'is_active',
        'has_discount',
        'discount_amount',
        'discount_is_percentage',
    ];

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'has_discount' => 'boolean',
            'discount_amount' => 'decimal:2',
            'discount_is_percentage' => 'boolean',
        ];
    }
}
