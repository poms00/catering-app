<?php

namespace App\Actions\Menu;

use App\Models\MenuItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MenuItemAction
{
    public function __construct(
        private readonly MenuImageAction $imageAction,
    ) {}

    public function index(MenuItem $item): array
    {
        $group = $item->menuGroup;
        $hasRenderableGroup = $group?->is_active === true;
        $categories = $item->menuCategories;
        $category = $categories->first() ?? $item->menuCategory ?? ($hasRenderableGroup ? $group->menuCategory : null);

        return [
            'id' => $item->id,
            'menu_group_id' => $item->menu_group_id,
            'menu_category_id' => $item->menu_category_id,
            'menu_category_ids' => $categories->pluck('id')->values()->all(),
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

            'menu_group' => $hasRenderableGroup ? [
                'id' => $group->id,
                'name' => $group->name,
                'menu_category_id' => $group->menu_category_id,
            ] : null,

            'menu_category' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
            ] : null,
            'menu_categories' => $categories
                ->map(fn ($category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                ])
                ->values()
                ->all(),
        ];
    }

    public function create(array $data, ?UploadedFile $image = null): MenuItem
    {
        return DB::transaction(function () use ($data, $image) {
            $groupId = $data['menu_group_id'] ?? null;
            $sortOrder = $data['sort_order'] ?? $this->nextSortOrder($groupId);
            $isDefault = $this->resolveDefaultState(
                groupId: $groupId,
                sortOrder: $sortOrder,
            );

            if ($isDefault && $groupId !== null) {
                $this->clearDefault($groupId);
            }

            $categoryIds = $this->normalizeCategoryIds($data);

            $item = MenuItem::create([
                'menu_group_id' => $groupId,
                'menu_category_id' => $categoryIds[0] ?? null,
                'name' => $data['name'],
                'slug' => $this->generateUniqueSlug($data['name']),
                'base_price' => $data['base_price'],
                'description' => $data['description'] ?? null,
                'is_default' => $isDefault,
                'sort_order' => $sortOrder,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $item->menuCategories()->sync($categoryIds);

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
            $groupId = array_key_exists('menu_group_id', $data)
                ? $data['menu_group_id']
                : $item->menu_group_id;
            $sortOrder = $data['sort_order'] ?? $item->sort_order;
            $isDefault = $this->resolveDefaultState(
                groupId: $groupId,
                sortOrder: $sortOrder,
            );

            if ($isDefault && $groupId !== null) {
                $this->clearDefault($groupId, excludeId: $item->id);
            }

            $categoryIds = $this->normalizeCategoryIds($data, $item);

            $item->update([
                'menu_group_id' => $groupId,
                'menu_category_id' => $categoryIds[0] ?? null,
                'name' => $data['name'] ?? $item->name,
                'base_price' => $data['base_price'] ?? $item->base_price,
                'description' => $data['description'] ?? $item->description,
                'is_default' => $isDefault,
                'sort_order' => $sortOrder,
                'is_active' => $data['is_active'] ?? $item->is_active,
            ]);

            $item->menuCategories()->sync($categoryIds);

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
            $item->images->each(fn ($img) => $this->imageAction->delete($img));
            $item->delete();
        });
    }

    public function reorder(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            foreach ($ids as $sortOrder => $id) {
                MenuItem::whereKey($id)->update([
                    'sort_order' => $sortOrder + 1,
                    'is_default' => $sortOrder === 0,
                ]);
            }
        });
    }

    private function resolveDefaultState(?int $groupId, int|string|null $sortOrder): bool
    {
        if ($groupId === null) {
            return true;
        }

        return (int) $sortOrder === 1;
    }

    /**
     * @return array<int, int>
     */
    private function normalizeCategoryIds(array $data, ?MenuItem $item = null): array
    {
        if (array_key_exists('menu_category_ids', $data) && is_array($data['menu_category_ids'])) {
            return collect($data['menu_category_ids'])
                ->filter(fn (mixed $id): bool => is_numeric($id))
                ->map(fn (mixed $id): int => (int) $id)
                ->filter(fn (int $id): bool => $id > 0)
                ->unique()
                ->values()
                ->all();
        }

        if (array_key_exists('menu_category_id', $data)) {
            return filled($data['menu_category_id']) ? [(int) $data['menu_category_id']] : [];
        }

        if ($item === null) {
            return [];
        }

        return $item->menuCategories()
            ->pluck('menu_categories.id')
            ->map(fn (int|string $id): int => (int) $id)
            ->values()
            ->all();
    }

    private function clearDefault(int $groupId, ?int $excludeId = null): void
    {
        MenuItem::where('menu_group_id', $groupId)
            ->where('is_default', true)
            ->when($excludeId, fn ($q) => $q->whereKeyNot($excludeId))
            ->update(['is_default' => false]);
    }

    private function nextSortOrder(?int $groupId): int
    {
        return ((int) MenuItem::when($groupId, fn ($q) => $q->where('menu_group_id', $groupId))
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
