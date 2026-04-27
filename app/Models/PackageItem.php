<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'package_id',
        'type',
        'menu_item_id',
        'menu_group_id',
        'default_menu_item_id',
        'qty',
        'min_select',
        'max_select',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function menuGroup(): BelongsTo
    {
        return $this->belongsTo(MenuGroup::class);
    }

    public function defaultMenuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'default_menu_item_id');
    }
}
