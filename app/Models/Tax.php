<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Tax extends BaseModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'rate',
        'type',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
