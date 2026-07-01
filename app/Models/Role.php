<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends BaseModel
{
    public const SYSTEM_ROLE_SLUGS = ['admin', 'client'];

    protected $fillable = ['name', 'slug', 'description'];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->slug = \Illuminate\Support\Str::slug($model->name);
        });

        static::updating(function ($model) {
            if ($model->isDirty('name')) {
                $model->slug = \Illuminate\Support\Str::slug($model->name);
            }
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }

    public function scopeEmployeeAssignable(Builder $query): Builder
    {
        return $query->whereNotIn('slug', self::SYSTEM_ROLE_SLUGS);
    }

    public function isSystemRole(): bool
    {
        return in_array($this->slug, self::SYSTEM_ROLE_SLUGS, true);
    }

    /**
     * @return array<int, string>
     */
    public static function systemRoleSlugs(): array
    {
        return self::SYSTEM_ROLE_SLUGS;
    }
}
