<?php

namespace App\Actions\Menu;

use App\Models\MenuCategory;
use App\Models\MenuGroup;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MenuGroupAction
{
    public function __construct(
        private readonly MenuImageAction $imageAction,
    ) {
    }

    public function index(MenuGroup $group): array
    {
        $category = $group->menuCategory;
        return [
            'id' => $group->id,
            'menu_category_id' => $group->menu_category_id,
            'name' => $group->name,
            'slug' => $group->slug,
            'description' => $group->description,
            'sort_order' => $group->sort_order,
            'is_active' => $group->is_active,
            'created_at' => $group->created_at?->toISOString(),
            'updated_at' => $group->updated_at?->toISOString(),
            'items_count' => $group->menu_items_count,
            'menu_category' => $category
                ? [
                    'id' => $category->id,
                    'name' => $category->name,
                ]
                : null,
        ];
    }

    public function create(array $data, ?UploadedFile $image = null): MenuGroup
    {
        return DB::transaction(function () use ($data, $image) {
            $group = MenuGroup::create([
                'menu_category_id' => $data['menu_category_id'] ?? null,
                'name' => $data['name'],
                'slug' => $this->generateUniqueSlug($data['name']),
                'description' => $data['description'] ?? null,
                'sort_order' => $data['sort_order'] ?? $this->nextSortOrder(),
                'is_active' => $data['is_active'] ?? true,
            ]);

            if ($image) {
                $this->imageAction->upload(
                    file: $image,
                    menuGroupId: $group->id,
                    isPrimary: true,
                );
            }

            return $group;
        });
    }

    public function update(MenuGroup $group, array $data, ?UploadedFile $image = null): MenuGroup
    {
        return DB::transaction(function () use ($group, $data, $image) {
            $group->update([
                'menu_category_id' => $data['menu_category_id'] ?? $group->menu_category_id,
                'name' => $data['name'] ?? $group->name,
                'description' => $data['description'] ?? $group->description,
                'sort_order' => $data['sort_order'] ?? $group->sort_order,
                'is_active' => $data['is_active'] ?? $group->is_active,
            ]);

            if ($image) {
                $this->imageAction->upload(
                    file: $image,
                    menuGroupId: $group->id,
                    isPrimary: true,
                );
            }

            return $group->refresh();
        });
    }

    public function delete(MenuGroup $group): void
    {
        DB::transaction(function () use ($group) {
            // Load semua relasi yang dibutuhkan sekaligus — 2 query saja
            $group->load(['images', 'items.images']);

            $group->images->each(fn($img) => $this->imageAction->delete($img));

            $group->items->each(function ($item) {
                $item->images->each(fn($img) => $this->imageAction->delete($img));
                $item->delete();
            });

            $group->delete();
        });
    }

    public function reorder(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            foreach ($ids as $sortOrder => $id) {
                MenuGroup::whereKey($id)->update(['sort_order' => $sortOrder + 1]);
            }
        });
    }

    private function nextSortOrder(): int
    {
        return ((int) MenuGroup::max('sort_order')) + 1;
    }

    private function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $suffix = 2;

        while (MenuGroup::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
