<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'description',
        'image_url',
        'is_customizable',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_customizable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function packageItems(): HasMany
    {
        return $this->hasMany(PackageItem::class);
    }
}
