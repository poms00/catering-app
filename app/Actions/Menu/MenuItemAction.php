<?php

namespace App\Actions\Menu;

use App\Models\MenuCategory;
use App\Models\MenuGroup;
use App\Models\MenuItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MenuItemAction
{
    public function __construct(
        private readonly MenuImageAction $imageAction,
    ) {
    }

    public function index(MenuItem $item): array
    {
        $group = $item->menuGroup;
        $category = $group?->menuCategory;

        return [
            'id' => $item->id,
            'menu_group_id' => $item->menu_group_id,
            'name' => $item->name,
            'slug' => $item->slug,
            'base_price' => $item->base_price,
            'description' => $item->description,
            'is_default' => $item->is_default,
            'sort_order' => $item->sort_order,
            'is_active' => $item->is_active,
            'created_at' => $item->created_at?->toISOString(),
            'updated_at' => $item->updated_at?->toISOString(),
            'primary_image' => $item->primaryImage?->image_url,

            'menu_group' => $group ? [
                'id' => $group->id,
                'name' => $group->name,
                'menu_category_id' => $group->menu_category_id,
            ] : null,

            'menu_category' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
            ] : null,
        ];
    }

    public function create(array $data, ?UploadedFile $image = null): MenuItem
    {
        return DB::transaction(function () use ($data, $image) {
            $isDefault = (bool) ($data['is_default'] ?? false);

            if ($isDefault && !empty($data['menu_group_id'])) {
                $this->clearDefault($data['menu_group_id']);
            }

            $item = MenuItem::create([
                'menu_group_id' => $data['menu_group_id'] ?? null,
                'name' => $data['name'],
                'slug' => $this->generateUniqueSlug($data['name']),
                'base_price' => $data['base_price'],
                'description' => $data['description'] ?? null,
                'is_default' => $isDefault,
                'sort_order' => $data['sort_order'] ?? $this->nextSortOrder($data['menu_group_id'] ?? null),
                'is_active' => $data['is_active'] ?? true,
            ]);

            if ($image) {
                $this->imageAction->upload(
                    file: $image,
                    menuItemId: $item->id,
                    isPrimary: true,
                );
            }

            return $item;
        });
    }

    public function update(MenuItem $item, array $data, ?UploadedFile $image = null): MenuItem
    {
        return DB::transaction(function () use ($item, $data, $image) {
            $isDefault = array_key_exists('is_default', $data)
                ? (bool) $data['is_default']
                : $item->is_default;

            $groupId = $data['menu_group_id'] ?? $item->menu_group_id;

            if ($isDefault && $groupId) {
                $this->clearDefault($groupId, excludeId: $item->id);
            }

            $item->update([
                'menu_group_id' => $groupId,
                'name' => $data['name'] ?? $item->name,
                'base_price' => $data['base_price'] ?? $item->base_price,
                'description' => $data['description'] ?? $item->description,
                'is_default' => $isDefault,
                'sort_order' => $data['sort_order'] ?? $item->sort_order,
                'is_active' => $data['is_active'] ?? $item->is_active,
            ]);

            if ($image) {
                $this->imageAction->upload(
                    file: $image,
                    menuItemId: $item->id,
                    isPrimary: true,
                );
            }

            return $item->refresh();
        });
    }

    public function delete(MenuItem $item): void
    {
        DB::transaction(function () use ($item) {
            $item->load('images');
            $item->images->each(fn($img) => $this->imageAction->delete($img));
            $item->delete();
        });
    }

    public function reorder(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            foreach ($ids as $sortOrder => $id) {
                MenuItem::whereKey($id)->update(['sort_order' => $sortOrder + 1]);
            }
        });
    }

    private function clearDefault(int $groupId, ?int $excludeId = null): void
    {
        MenuItem::where('menu_group_id', $groupId)
            ->where('is_default', true)
            ->when($excludeId, fn($q) => $q->whereKeyNot($excludeId))
            ->update(['is_default' => false]);
    }

    private function nextSortOrder(?int $groupId): int
    {
        return ((int) MenuItem::when($groupId, fn($q) => $q->where('menu_group_id', $groupId))
            ->max('sort_order')) + 1;
    }

    private function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $suffix = 2;

        while (MenuItem::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
