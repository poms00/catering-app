<?php

namespace App\Actions\Menu;

use App\Models\MenuCategory;
use App\Models\MenuGroup;
use App\Models\MenuItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class MenuCategoryAction
{
      public function index(MenuCategory $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'sort_order' => $category->sort_order,
            'is_active' => $category->is_active,
            'groups' => $category->menuGroups->map(fn(MenuGroup $group): array => [
                'id' => $group->id,
                'menu_category_id' => $group->menu_category_id,
                'name' => $group->name,
                'slug' => $group->slug,
                'description' => $group->description,
                'sort_order' => $group->sort_order,
                'is_active' => $group->is_active,
            ])->values()->all(),
        ];
    }

    public function create(array $data): MenuCategory
    {
        return DB::transaction(function () use ($data) {
            $category = MenuCategory::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'sort_order' => $data['sort_order'] ?? $this->nextSortOrder(),
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $category;
        });
    }

    public function update(MenuCategory $category, array $data): MenuCategory
    {
        return DB::transaction(function () use ($category, $data) {
            $category->update([
                'name' => $data['name'] ?? $category->name,
                'slug' => $data['slug'] ?? $category->slug,
                'description' => $data['description'] ?? $category->description,
                'sort_order' => $data['sort_order'] ?? $category->sort_order,
                'is_active' => $data['is_active'] ?? $category->is_active,
            ]);

            return $category->refresh();
        });
    }

    public function delete(MenuCategory $category): void
    {
        DB::transaction(function () use ($category) {
            $category->delete();
        });
    }

    public function reorder(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            foreach ($ids as $sortOrder => $id) {
                MenuCategory::whereKey($id)->update(['sort_order' => $sortOrder + 1]);
            }
        });
    }

    private function nextSortOrder(): int
    {
        return ((int) MenuCategory::max('sort_order')) + 1;
    }
}
  