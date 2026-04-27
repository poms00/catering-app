<?php

namespace App\Actions\Menu;

use App\Models\MenuCategory;
use App\Models\MenuGroup;
use App\Models\MenuItem;
use Illuminate\Support\Collection;

class IndexDataAction
{
    public function menuItems(): Collection
    {
        return MenuItem::query()
            ->select(['id', 'menu_group_id', 'name', 'slug', 'base_price', 'description', 'is_default', 'sort_order', 'is_active', 'created_at', 'updated_at'])
            ->with([
                'menuGroup:id,menu_category_id,name',
                'menuGroup.menuCategory:id,name',
                'primaryImage:id,menu_item_id,image_url',
            ])
            ->ordered()
            ->get();
    }

    public function menuGroups(): Collection
    {
        return MenuGroup::query()
            ->select(['id', 'menu_category_id', 'name', 'slug', 'description', 'sort_order', 'is_active', 'created_at', 'updated_at'])
            ->with([
                'menuCategory:id,name',
                'menuItems:id,menu_group_id',
            ])
            ->ordered()
            ->get();
    }

    public function menuCategories(): Collection
    {
        return MenuCategory::query()
            ->select(['id', 'name', 'slug', 'description', 'sort_order', 'is_active'])
            ->with([
                'menuGroups' => fn($q) => $q
                    ->select(['id', 'menu_category_id', 'name', 'slug', 'description', 'sort_order', 'is_active'])
                    ->ordered(),
            ])
            ->ordered()
            ->get();
    }
}