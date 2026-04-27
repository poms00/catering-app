<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_group_id',
        'name',
        'slug',
        'base_price',
        'description',
        'is_default',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function menuGroup(): BelongsTo
    {
        return $this->belongsTo(MenuGroup::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(MenuImage::class);
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(MenuImage::class)
            ->where('is_primary', true)
            ->orderBy('sort_order');
    }

    public function packageItems(): HasMany
    {
        return $this->hasMany(PackageItem::class);
    }

    public function defaultForPackageItems(): HasMany
    {
        return $this->hasMany(PackageItem::class, 'default_menu_item_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
