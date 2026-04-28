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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Response;

class MenuController extends Controller
{
    private const DETAIL_PAGE = 'admin/menu/show-detail-menu';

    private const EDIT_PAGE = 'admin/menu/edit-menu';

    private const MENU_TYPE_GROUP = 'group';

    private const MENU_TYPE_ITEM = 'item';

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
            'grup' => null,
            'menuType' => 'create',
            'varianList' => [],
            ...$this->menuFormOptions(),
        ]);
    }

    public function store(StoreMenuGroupRequest $request): RedirectResponse
    {
        $validated = $this->resolveCategoryDrafts($request->validated());

        if (isset($validated['entries']) && is_array($validated['entries'])) {
            return $this->storeBuilderEntries($validated['entries']);
        }

        $variants = $validated['variants'];

        if (! empty($validated['menu_group_id'])) {
            $group = MenuGroup::findOrFail($validated['menu_group_id']);
            $this->createVariantsForGroup($group, $variants);

            return redirect()
                ->route('menu.index')
                ->with('success', "Menu berhasil ditambahkan ke grup \"{$group->name}\".");
        }

        if ($request->boolean('creates_with_group')) {
            $group = $this->groupAction->create(
                data: $validated,
                image: $request->file('image'),
            );
            $this->createVariantsForGroup($group, $variants);

            return redirect()
                ->route('menu.index')
                ->with('success', "Grup \"{$group->name}\" berhasil dibuat.");
        }

        $variant = $variants[0];

        if (! array_key_exists('menu_category_id', $variant)) {
            $variant['menu_category_id'] = $validated['menu_category_id'] ?? null;
        }

        if (! array_key_exists('menu_category_ids', $variant) && isset($validated['menu_category_ids'])) {
            $variant['menu_category_ids'] = $validated['menu_category_ids'];
        }

        $item = $this->createStandaloneItem($variant);

        return redirect()
            ->route('menu.index')
            ->with('success', "Menu \"{$item->name}\" berhasil dibuat.");
    }

    private function storeBuilderEntries(array $entries): RedirectResponse
    {
        [$createdWrapperCount, $createdSingleCount] = DB::transaction(
            function () use ($entries): array {
                $wrapperOrder = 1;
                $singleOrder = 1;
                $createdWrapperCount = 0;
                $createdSingleCount = 0;

                foreach ($entries as $entry) {
                    if (($entry['type'] ?? null) === self::MENU_TYPE_GROUP || ($entry['type'] ?? null) === 'wrapper') {
                        $group = $this->groupAction->create(
                            data: [
                                'menu_category_id' => $entry['menu_category_id'] ?? null,
                                'name' => $entry['name'] ?? '',
                                'description' => $entry['description'] ?? null,
                                'sort_order' => $wrapperOrder,
                                'is_active' => $entry['is_active'] ?? true,
                            ],
                            image: $entry['image'] ?? null,
                        );

                        $this->createVariantsForGroup(
                            $group,
                            $entry['variants'] ?? [],
                        );

                        $wrapperOrder++;
                        $createdWrapperCount++;

                        continue;
                    }

                    $variant = $entry['variants'][0] ?? null;

                    if (! is_array($variant)) {
                        continue;
                    }

                    $this->itemAction->create(
                        data: [
                            ...$variant,
                            'sort_order' => $singleOrder,
                            'is_default' => true,
                            'menu_group_id' => null,
                        ],
                        image: $variant['image'] ?? null,
                    );

                    $singleOrder++;
                    $createdSingleCount++;
                }

                return [$createdWrapperCount, $createdSingleCount];
            },
        );

        $createdEntryCount = $createdWrapperCount + $createdSingleCount;

        return redirect()
            ->route('menu.index')
            ->with(
                'success',
                "{$createdEntryCount} entri menu berhasil dibuat."
            );
    }

    public function show(Request $request, string $menu): Response
    {
        if ($this->wantsStandaloneItem($request)) {
            return $this->renderStandaloneItemShowPage(
                $this->findStandaloneItemOrFail($menu),
            );
        }

        $group = MenuGroup::query()->find($menu);

        if ($group instanceof MenuGroup) {
            return $this->renderGroupShowPage($group);
        }

        return $this->renderStandaloneItemShowPage(
            $this->findStandaloneItemOrFail($menu),
        );
    }

    public function edit(Request $request, string $menu): Response|RedirectResponse
    {
        if ($this->wantsStandaloneItem($request)) {
            return $this->renderStandaloneItemEditPage(
                $this->findStandaloneItemOrFail($menu),
            );
        }

        $group = MenuGroup::query()->find($menu);

        if (! $group instanceof MenuGroup) {
            $item = MenuItem::query()->findOrFail($menu);

            if ($item->menu_group_id !== null) {
                return redirect()->route('menu.edit', $item->menu_group_id);
            }

            return $this->renderStandaloneItemEditPage($item);
        }

        return $this->renderGroupEditPage($group);
    }

    public function update(UpdateMenuGroupRequest $request, string $menu): RedirectResponse
    {
        $validated = $request->validated();
        $validated['variants'] = $this->preserveVariantGroupSelections(
            $request->input('variants', []),
            $validated['variants'] ?? [],
        );
        $validated = $this->resolveCategoryDrafts($validated);

        if ($this->wantsStandaloneItem($request)) {
            return $this->updateStandaloneItem($menu, $validated, $request);
        }

        $group = MenuGroup::query()->findOrFail($menu);

        $this->groupAction->update(
            group: $group,
            data: $validated,
            image: $request->file('image'),
        );
        $this->groupAction->syncVariants($group, $validated['variants']);

        $updatedGroup = $group->fresh();

        return $this->redirectToGroupShowPage(
            $updatedGroup,
            "Grup \"{$updatedGroup->name}\" berhasil diperbarui.",
        );
    }

    private function renderGroupShowPage(MenuGroup $group): Response
    {
        return $this->renderShowPage(
            $this->loadGroupDetailRelations($group),
            self::MENU_TYPE_GROUP,
        );
    }

    private function renderStandaloneItemShowPage(MenuItem $item): Response
    {
        return $this->renderShowPage(
            $this->standaloneItemGroupPayload($item),
            self::MENU_TYPE_ITEM,
        );
    }

    private function renderGroupEditPage(MenuGroup $group): Response
    {
        $loadedGroup = $this->loadGroupDetailRelations($group);
        $variantList = $loadedGroup->menuItems
            ->map(fn (MenuItem $item): array => $this->editVariantPayload($item))
            ->values()
            ->all();

        return $this->renderEditPage(
            group: $loadedGroup,
            variantList: $variantList,
            menuType: self::MENU_TYPE_GROUP,
        );
    }

    private function renderStandaloneItemEditPage(MenuItem $item): Response
    {
        return $this->renderEditPage(
            group: null,
            variantList: [$this->editVariantPayload($item)],
            menuType: self::MENU_TYPE_ITEM,
            menuId: $item->id,
        );
    }

    private function updateStandaloneItem(
        string $itemId,
        array $validated,
        UpdateMenuGroupRequest $request,
    ): RedirectResponse {
        $item = MenuItem::query()
            ->whereNull('menu_group_id')
            ->findOrFail($itemId);
        $variant = $validated['variants'][0];
        $image = $variant['image'] ?? null;

        $this->itemAction->update(
            item: $item,
            data: [
                ...$variant,
                'is_default' => true,
                'menu_group_id' => $variant['menu_group_id'] ?? null,
            ],
            image: $image,
        );

        $updatedItem = $item->fresh();

        if ($updatedItem->menu_group_id !== null) {
            $targetGroup = MenuGroup::query()->findOrFail($updatedItem->menu_group_id);

            return $this->redirectToGroupShowPage(
                $targetGroup,
                "Menu \"{$updatedItem->name}\" berhasil dipindahkan ke grup.",
            );
        }

        return $this->redirectToStandaloneItemShowPage(
            $updatedItem,
            "Menu \"{$updatedItem->name}\" berhasil diperbarui.",
        );
    }

    private function createVariantsForGroup(MenuGroup $group, array $variants): void
    {
        foreach ($variants as $variant) {
            if (! array_key_exists('menu_category_id', $variant)) {
                $variant['menu_category_id'] = $group->menu_category_id;
            }

            if (! array_key_exists('menu_category_ids', $variant)) {
                $variant['menu_category_ids'] = filled($variant['menu_category_id'] ?? null)
                    ? [(int) $variant['menu_category_id']]
                    : [];
            }

            $this->itemAction->create(
                data: [
                    ...$variant,
                    'menu_group_id' => $group->id,
                ],
                image: $variant['image'] ?? null,
            );
        }
    }

    private function createStandaloneItem(array $variant): MenuItem
    {
        return $this->itemAction->create(
            data: [
                ...$variant,
                'is_default' => true,
                'menu_group_id' => null,
            ],
            image: $variant['image'] ?? null,
        );
    }

    private function resolveCategoryDrafts(array $data): array
    {
        $categoryIdsByTempId = [];

        foreach (($data['category_drafts'] ?? []) as $draft) {
            if (! is_array($draft)) {
                continue;
            }

            $tempId = (int) ($draft['temp_id'] ?? 0);
            $name = trim((string) ($draft['name'] ?? ''));

            if ($tempId >= 0 || $name === '') {
                continue;
            }

            $category = MenuCategory::query()->firstOrCreate(
                ['name' => $name],
                [
                    'slug' => $this->generateUniqueCategorySlug($name),
                    'sort_order' => $this->nextCategorySortOrder(),
                    'is_active' => true,
                ],
            );

            $categoryIdsByTempId[$tempId] = $category->id;
        }

        unset($data['category_drafts']);

        $data['menu_category_id'] = $this->resolveCategoryId(
            $data['menu_category_id'] ?? null,
            $categoryIdsByTempId,
        );
        $data['menu_category_ids'] = $this->resolveCategoryIds(
            $data['menu_category_ids'] ?? [],
            $categoryIdsByTempId,
        );
        if ($data['menu_category_ids'] === [] && $data['menu_category_id'] !== null) {
            $data['menu_category_ids'] = [$data['menu_category_id']];
        }
        $data['menu_category_id'] ??= $data['menu_category_ids'][0] ?? null;

        foreach (($data['entries'] ?? []) as $entryIndex => $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $data['entries'][$entryIndex]['menu_category_id'] = $this->resolveCategoryId(
                $entry['menu_category_id'] ?? null,
                $categoryIdsByTempId,
            );
            $data['entries'][$entryIndex]['menu_category_ids'] = $this->resolveCategoryIds(
                $entry['menu_category_ids'] ?? [],
                $categoryIdsByTempId,
            );
            if ($data['entries'][$entryIndex]['menu_category_ids'] === [] && $data['entries'][$entryIndex]['menu_category_id'] !== null) {
                $data['entries'][$entryIndex]['menu_category_ids'] = [$data['entries'][$entryIndex]['menu_category_id']];
            }
            $data['entries'][$entryIndex]['menu_category_id'] ??= $data['entries'][$entryIndex]['menu_category_ids'][0] ?? null;

            foreach (($entry['variants'] ?? []) as $variantIndex => $variant) {
                if (! is_array($variant)) {
                    continue;
                }

                $data['entries'][$entryIndex]['variants'][$variantIndex]['menu_category_id'] = $this->resolveCategoryId(
                    $variant['menu_category_id'] ?? null,
                    $categoryIdsByTempId,
                );
                $data['entries'][$entryIndex]['variants'][$variantIndex]['menu_category_ids'] = $this->resolveCategoryIds(
                    $variant['menu_category_ids'] ?? [],
                    $categoryIdsByTempId,
                );
                if ($data['entries'][$entryIndex]['variants'][$variantIndex]['menu_category_ids'] === [] && $data['entries'][$entryIndex]['variants'][$variantIndex]['menu_category_id'] !== null) {
                    $data['entries'][$entryIndex]['variants'][$variantIndex]['menu_category_ids'] = [$data['entries'][$entryIndex]['variants'][$variantIndex]['menu_category_id']];
                }
                $data['entries'][$entryIndex]['variants'][$variantIndex]['menu_category_id'] ??= $data['entries'][$entryIndex]['variants'][$variantIndex]['menu_category_ids'][0] ?? null;
            }
        }

        foreach (($data['variants'] ?? []) as $variantIndex => $variant) {
            if (! is_array($variant)) {
                continue;
            }

            $data['variants'][$variantIndex]['menu_category_id'] = $this->resolveCategoryId(
                $variant['menu_category_id'] ?? null,
                $categoryIdsByTempId,
            );
            $data['variants'][$variantIndex]['menu_category_ids'] = $this->resolveCategoryIds(
                $variant['menu_category_ids'] ?? [],
                $categoryIdsByTempId,
            );
            if ($data['variants'][$variantIndex]['menu_category_ids'] === [] && $data['variants'][$variantIndex]['menu_category_id'] !== null) {
                $data['variants'][$variantIndex]['menu_category_ids'] = [$data['variants'][$variantIndex]['menu_category_id']];
            }
            $data['variants'][$variantIndex]['menu_category_id'] ??= $data['variants'][$variantIndex]['menu_category_ids'][0] ?? null;
        }

        return $data;
    }

    /**
     * @param  array<int, int>  $categoryIdsByTempId
     */
    private function resolveCategoryId(mixed $categoryId, array $categoryIdsByTempId): ?int
    {
        if ($categoryId === null || $categoryId === '') {
            return null;
        }

        $categoryId = (int) $categoryId;

        if ($categoryId < 0) {
            return $categoryIdsByTempId[$categoryId] ?? null;
        }

        return $categoryId;
    }

    /**
     * @param  array<int, int>  $categoryIdsByTempId
     * @return array<int, int>
     */
    private function resolveCategoryIds(mixed $categoryIds, array $categoryIdsByTempId): array
    {
        if (! is_array($categoryIds)) {
            return [];
        }

        return collect($categoryIds)
            ->map(fn (mixed $categoryId): ?int => $this->resolveCategoryId($categoryId, $categoryIdsByTempId))
            ->filter(fn (?int $categoryId): bool => $categoryId !== null)
            ->unique()
            ->values()
            ->all();
    }

    private function nextCategorySortOrder(): int
    {
        return ((int) MenuCategory::query()->max('sort_order')) + 1;
    }

    private function generateUniqueCategorySlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'kategori';
        $slug = $baseSlug;
        $suffix = 2;

        while (MenuCategory::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function findStandaloneItemOrFail(string $itemId): MenuItem
    {
        return MenuItem::query()
            ->whereNull('menu_group_id')
            ->with([
                'menuCategory:id,name',
                'menuCategories:id,name',
                'images' => fn ($query) => $query->orderBy('sort_order'),
            ])
            ->findOrFail($itemId);
    }

    private function loadGroupDetailRelations(MenuGroup $group): MenuGroup
    {
        $group->load([
            'menuCategory:id,name',
            'images' => fn ($query) => $query->orderBy('sort_order'),
            'menuItems' => fn ($query) => $query->ordered(),
            'menuItems.menuCategory:id,name',
            'menuItems.menuCategories:id,name',
            'menuItems.menuGroup:id,menu_category_id',
            'menuItems.images' => fn ($query) => $query->orderBy('sort_order'),
        ]);

        return $group;
    }

    private function renderShowPage(MenuGroup|array $group, string $menuType): Response
    {
        return inertia(self::DETAIL_PAGE, [
            'group' => $group,
            'canEdit' => true,
            'menuType' => $menuType,
        ]);
    }

    private function renderEditPage(
        ?MenuGroup $group,
        array $variantList,
        string $menuType,
        ?int $menuId = null,
    ): Response {
        $props = [
            'grup' => $group,
            'varianList' => $variantList,
            ...$this->menuFormOptions(),
            'menuType' => $menuType,
        ];

        if ($menuId !== null) {
            $props['menuId'] = $menuId;
        }

        return inertia(self::EDIT_PAGE, $props);
    }

    private function menuFormOptions(): array
    {
        return [
            'menuCategories' => MenuCategory::ordered()->get(['id', 'name']),
            'menuGroups' => MenuGroup::ordered()->get(['id', 'name', 'menu_category_id', 'is_active']),
        ];
    }

    private function wantsStandaloneItem(Request $request): bool
    {
        return $request->query('type') === self::MENU_TYPE_ITEM;
    }

    private function editVariantPayload(MenuItem $item): array
    {
        return [
            'id' => $item->id,
            'menu_group_id' => $item->menu_group_id,
            'menu_category_id' => $item->menu_category_id,
            'menu_category_ids' => $item->menuCategories->pluck('id')->values()->all(),
            'name' => $item->name,
            'image_url' => $item->images->first()?->image_url,
            'base_price' => $item->base_price,
            'description' => $item->description,
            'is_active' => $item->is_active,
            'is_default' => $item->is_default,
            'sort_order' => $item->sort_order,
        ];
    }

    private function standaloneItemGroupPayload(MenuItem $item): array
    {
        return [
            'id' => $item->id,
            'menu_category_id' => $item->menu_category_id,
            'menu_category_ids' => $item->menuCategories->pluck('id')->values()->all(),
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
            'menu_category' => $item->menuCategory
                ? [
                    'id' => $item->menuCategory->id,
                    'name' => $item->menuCategory->name,
                ]
                : null,
            'menu_categories' => $item->menuCategories
                ->map(fn (MenuCategory $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                ])
                ->values()
                ->all(),
            'menu_items' => [
                [
                    'id' => $item->id,
                    'menu_group_id' => null,
                    'menu_category_id' => $item->menu_category_id,
                    'menu_category_ids' => $item->menuCategories->pluck('id')->values()->all(),
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
                    'images' => $item->images
                        ->map(fn (MenuImage $image): array => $this->menuImagePayload($image))
                        ->values(),
                    'primary_image' => $item->images->firstWhere('is_primary', true)?->image_url,
                    'menu_group' => null,
                    'menu_category' => $item->menuCategory
                        ? [
                            'id' => $item->menuCategory->id,
                            'name' => $item->menuCategory->name,
                        ]
                        : null,
                    'menu_categories' => $item->menuCategories
                        ->map(fn (MenuCategory $category): array => [
                            'id' => $category->id,
                            'name' => $category->name,
                        ])
                        ->values()
                        ->all(),
                ],
            ],
        ];
    }

    private function menuImagePayload(MenuImage $image): array
    {
        return [
            'id' => $image->id,
            'menu_item_id' => $image->menu_item_id,
            'menu_group_id' => $image->menu_group_id,
            'image_url' => $image->image_url,
            'is_primary' => $image->is_primary,
            'sort_order' => $image->sort_order,
            'created_at' => $image->created_at?->toISOString(),
        ];
    }

    private function preserveVariantGroupSelections(array $requestedVariants, array $validatedVariants): array
    {
        return collect($validatedVariants)
            ->map(function (array $variant, int $index) use ($requestedVariants): array {
                if (! array_key_exists($index, $requestedVariants)) {
                    return $variant;
                }

                $requestedVariant = $requestedVariants[$index];

                if (! is_array($requestedVariant) || ! array_key_exists('menu_group_id', $requestedVariant)) {
                    return $variant;
                }

                $variant['menu_group_id'] = filled($requestedVariant['menu_group_id'])
                    ? (int) $requestedVariant['menu_group_id']
                    : null;

                return $variant;
            })
            ->values()
            ->all();
    }

    private function redirectToGroupShowPage(MenuGroup $group, string $message): RedirectResponse
    {
        return redirect()
            ->route('menu.show', $group)
            ->with('success', $message);
    }

    private function redirectToStandaloneItemShowPage(MenuItem $item, string $message): RedirectResponse
    {
        return redirect()
            ->route('menu.show', [
                'menu' => $item->id,
                'type' => self::MENU_TYPE_ITEM,
            ])
            ->with('success', $message);
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
