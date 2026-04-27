<?php

namespace App\Http\Controllers;

use App\Actions\Menu\IndexDataAction;
use App\Actions\Menu\MenuCategoryAction;
use App\Actions\Menu\MenuGroupAction;
use App\Actions\Menu\MenuImageAction;
use App\Actions\Menu\MenuItemAction;
use App\Http\Requests\Menu\ReorderRequest;
use App\Http\Requests\Menu\StoreMenuGroupRequest;
use App\Http\Requests\Menu\StoreMenuItemRequest;
use App\Http\Requests\Menu\UpdateMenuGroupRequest;
use App\Http\Requests\Menu\UpdateMenuItemRequest;
use App\Models\MenuCategory;
use App\Models\MenuGroup;
use App\Models\MenuImage;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class MenuController extends Controller
{
    public function __construct(
        private readonly MenuCategoryAction $categoryAction,
        private readonly MenuGroupAction $groupAction,
        private readonly MenuItemAction $itemAction,
        private readonly MenuImageAction $imageAction,
        private readonly IndexDataAction $indexDataAction,
    ) {}

    /* ════════════════════════════════════════
       GRUP
    ════════════════════════════════════════ */

    public function index(): Response
    {
        $menuItems = $this->indexDataAction->menuItems()
            ->map(fn (MenuItem $item) => $this->itemAction->index($item));

        $menuGroups = $this->indexDataAction->menuGroups()
            ->map(fn (MenuGroup $group) => $this->groupAction->index($group));

        $menuCategories = $this->indexDataAction->menuCategories()
            ->map(fn (MenuCategory $category) => $this->categoryAction->index($category));

        return inertia('admin/menu/index', compact('menuItems', 'menuGroups', 'menuCategories'));
    }

    public function create(): Response
    {
        return inertia('admin/menu/create-menu', [
            'menuCategories' => MenuCategory::active()->ordered()->get(['id', 'name']),
            'menuGroups' => MenuGroup::active()->ordered()->get(['id', 'name', 'menu_category_id']),
        ]);
    }

    public function store(StoreMenuGroupRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $variants = $validated['variants'];

        // Pakai grup yang sudah ada
        if (!empty($validated['menu_group_id'])) {
            $group = MenuGroup::findOrFail($validated['menu_group_id']);

            foreach ($variants as $variant) {
                $this->itemAction->create([
                    ...$variant,
                    'menu_group_id' => $group->id,
                ]);
            }

            return redirect()
                ->route('menu.show', $group)
                ->with('success', "Menu berhasil ditambahkan ke grup \"{$group->name}\".");
        }

        // Buat grup baru
        if ($request->boolean('creates_with_group')) {
            $group = $this->groupAction->create(
                data: $validated,
                image: $request->file('image'),
            );

            foreach ($variants as $variant) {
                $this->itemAction->create([
                    ...$variant,
                    'menu_group_id' => $group->id,
                ]);
            }

            return redirect()
                ->route('menu.show', $group)
                ->with('success', "Grup \"{$group->name}\" berhasil dibuat.");
        }

        // Standalone item
        $variant = $variants[0];
        $item = $this->itemAction->create([
            ...$variant,
            'is_default' => true,
        ]);

        return redirect()
            ->route('menu.show', ['menu' => $item, 'type' => 'item'])
            ->with('success', "Menu \"{$item->name}\" berhasil dibuat.");
    }

    public function show(Request $request, string $menu): Response
    {
        $group = MenuGroup::query()->find($menu);

        if ($request->query('type') === 'item') {
            return $this->showStandaloneItem($menu);
        }

        if ($group instanceof MenuGroup) {
            $group->load([
                'menuCategory:id,name',
                'images' => fn ($q) => $q->orderBy('sort_order'),
                'menuItems' => fn ($q) => $q->ordered(),
                'menuItems.images' => fn ($q) => $q->orderBy('sort_order'),
            ]);

            return inertia('admin/menu/show-detail-menu', [
                'group' => $group,
                'canEdit' => true,
            ]);
        }

        return $this->showStandaloneItem($menu);
    }

    private function showStandaloneItem(string $itemId): Response
    {
        $item = MenuItem::query()
            ->whereNull('menu_group_id')
            ->with([
                'images' => fn ($q) => $q->orderBy('sort_order'),
            ])
            ->findOrFail($itemId);

        return inertia('admin/menu/show-detail-menu', [
            'group' => [
                'id' => $item->id,
                'menu_category_id' => null,
                'name' => $item->name,
                'slug' => $item->slug,
                'description' => $item->description,
                'sort_order' => $item->sort_order,
                'is_active' => $item->is_active,
                'created_at' => $item->created_at?->toISOString(),
                'updated_at' => $item->updated_at?->toISOString(),
                'created_by' => $item->created_by,
                'updated_by' => $item->updated_by,
                'images' => [],
                'menu_category' => null,
                'menu_items' => [
                    [
                        'id' => $item->id,
                        'menu_group_id' => null,
                        'menu_category_id' => null,
                        'name' => $item->name,
                        'slug' => $item->slug,
                        'base_price' => $item->base_price,
                        'description' => $item->description,
                        'is_default' => $item->is_default,
                        'sort_order' => $item->sort_order,
                        'is_active' => $item->is_active,
                        'created_at' => $item->created_at?->toISOString(),
                        'updated_at' => $item->updated_at?->toISOString(),
                        'created_by' => $item->created_by,
                        'updated_by' => $item->updated_by,
                        'images' => $item->images->map(fn (MenuImage $image) => [
                            'id' => $image->id,
                            'menu_item_id' => $image->menu_item_id,
                            'menu_group_id' => $image->menu_group_id,
                            'image_url' => $image->image_url,
                            'is_primary' => $image->is_primary,
                            'sort_order' => $image->sort_order,
                            'created_at' => $image->created_at?->toISOString(),
                        ])->values(),
                        'primary_image' => $item->images
                            ->firstWhere('is_primary', true)?->image_url,
                        'menu_group' => null,
                        'menu_category' => null,
                    ],
                ],
            ],
            'canEdit' => false,
        ]);
    }

    public function edit(MenuGroup $menu): Response
    {
        $group = $menu;

        $group->load([
            'menuCategory:id,name',
            'images' => fn ($q) => $q->orderBy('sort_order'),
            'menuItems' => fn ($q) => $q->ordered(),
            'menuItems.images' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        $categories = MenuCategory::active()->ordered()->get(['id', 'name']);

        $itemList = $group->menuItems->map(fn (MenuItem $item) => [
            'id' => $item->id,
            'name' => $item->name,
            'image_url' => $item->images->first()?->image_url,
            'base_price' => $item->base_price,
            'is_active' => $item->is_active,
            'is_default' => $item->is_default,
            'sort_order' => $item->sort_order,
        ]);

        return inertia('admin/menu/edit', [
            'group' => $group,
            'itemList' => $itemList,
            'categories' => $categories,
        ]);
    }

    public function update(UpdateMenuGroupRequest $request, MenuGroup $menu): RedirectResponse
    {
        $group = $menu;

        $this->groupAction->update(
            group: $group,
            data: $request->validated(),
            image: $request->file('image'),
        );

        return back()->with('success', "Grup \"{$group->name}\" berhasil diperbarui.");
    }

    public function destroy(MenuGroup $menu): RedirectResponse
    {
        $group = $menu;
        $name = $group->name;

        $this->groupAction->delete($group);

        return redirect()
            ->route('menu.index')
            ->with('success', "Grup \"{$name}\" berhasil dihapus.");
    }

    /* ════════════════════════════════════════
       ITEM
    ════════════════════════════════════════ */

    public function storeItem(StoreMenuItemRequest $request, MenuGroup $group): RedirectResponse
    {
        $this->itemAction->create(
            data: array_merge($request->validated(), ['menu_group_id' => $group->id]),
            image: $request->file('image'),
        );

        return back()->with('success', 'Item berhasil ditambahkan.');
    }

    public function updateItem(UpdateMenuItemRequest $request, MenuGroup $group, MenuItem $item): RedirectResponse
    {
        $this->itemAction->update(
            item: $item,
            data: $request->validated(),
            image: $request->file('image'),
        );

        return back()->with('success', "Item \"{$item->name}\" berhasil diperbarui.");
    }

    public function destroyItem(MenuGroup $group, MenuItem $item): RedirectResponse
    {
        $name = $item->name;

        $this->itemAction->delete($item);

        return back()->with('success', "Item \"{$name}\" berhasil dihapus.");
    }

    public function reorderItems(ReorderRequest $request, MenuGroup $group): RedirectResponse
    {
        $this->itemAction->reorder($request->validated('urutan'));

        return back()->with('success', 'Urutan item berhasil disimpan.');
    }

    /* ════════════════════════════════════════
       IMAGE
    ════════════════════════════════════════ */

    public function storeImage(Request $request, MenuGroup $group, ?MenuItem $item = null): RedirectResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'is_primary' => ['boolean'],
        ]);

        $this->imageAction->upload(
            file: $request->file('image'),
            menuGroupId: $item ? null : $group->id,
            menuItemId: $item?->id,
            isPrimary: $request->boolean('is_primary'),
        );

        return back()->with('success', 'Foto berhasil diupload.');
    }

    public function setPrimaryImage(MenuGroup $group, MenuImage $image): RedirectResponse
    {
        $this->imageAction->setPrimary($image);

        return back()->with('success', 'Foto utama berhasil diubah.');
    }

    public function destroyImage(MenuGroup $group, MenuImage $image): RedirectResponse
    {
        $this->imageAction->delete($image);

        return back()->with('success', 'Foto berhasil dihapus.');
    }
}
