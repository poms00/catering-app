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
        return $this->sortMenuItemsForIndex(
            MenuItem::query()
                ->select(['id', 'menu_group_id', 'menu_category_id', 'name', 'slug', 'base_price', 'description', 'is_default', 'sort_order', 'is_active', 'created_at', 'updated_at'])
                ->with([
                    'menuCategory:id,name',
                    'menuCategories:id,name',
                    'menuGroup:id,menu_category_id,name,is_active',
                    'menuGroup.menuCategory:id,name',
                    'primaryImage:id,menu_item_id,image_url',
                ])
                ->ordered()
                ->get()
        );
    }

    public function menuGroups(): Collection
    {
        return MenuGroup::query()
            ->select(['id', 'menu_category_id', 'name', 'slug', 'description', 'sort_order', 'is_active', 'created_at', 'updated_at'])
            ->with([
                'menuCategory:id,name',
            ])
            ->withCount('menuItems')
            ->ordered()
            ->get();
    }

    public function menuCategories(): Collection
    {
        return MenuCategory::query()
            ->select(['id', 'name', 'slug', 'description', 'sort_order', 'is_active'])
            ->with([
                'menuGroups' => fn ($q) => $q
                    ->select(['id', 'menu_category_id', 'name', 'slug', 'description', 'sort_order', 'is_active'])
                    ->ordered(),
            ])
            ->ordered()
            ->get();
    }

    /**
     * @param  Collection<int, MenuItem>  $items
     * @return Collection<int, MenuItem>
     */
    private function sortMenuItemsForIndex(Collection $items): Collection
    {
        return $items
            ->sort(function (MenuItem $firstItem, MenuItem $secondItem): int {
                $groupComparison = $this->compareValues(
                    $firstItem->menu_group_id ?? $firstItem->id,
                    $secondItem->menu_group_id ?? $secondItem->id,
                );

                if ($groupComparison !== 0) {
                    return $groupComparison;
                }

                $defaultComparison = $this->compareValues(
                    (int) $secondItem->is_default,
                    (int) $firstItem->is_default,
                );

                if ($defaultComparison !== 0) {
                    return $defaultComparison;
                }

                $sortOrderComparison = $this->compareValues(
                    $firstItem->sort_order,
                    $secondItem->sort_order,
                );

                if ($sortOrderComparison !== 0) {
                    return $sortOrderComparison;
                }

                return $this->compareValues($firstItem->name, $secondItem->name);
            })
            ->values();
    }

    private function compareValues(int|string|null $firstValue, int|string|null $secondValue): int
    {
        return ($firstValue ?? 0) <=> ($secondValue ?? 0);
    }
}
