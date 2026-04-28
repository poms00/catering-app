<?php

namespace Tests\Feature\Menu;

use App\Models\MenuCategory;
use App\Models\MenuGroup;
use App\Models\MenuImage;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MenuEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_menu_page_displays_menu_and_group_options_for_the_form(): void
    {
        $user = User::factory()->create();
        $category = MenuCategory::factory()->create([
            'name' => 'Paket Box',
        ]);
        $menuGroup = MenuGroup::factory()->create([
            'menu_category_id' => $category->id,
            'name' => 'Nasi Box',
        ]);
        $menu = MenuItem::factory()->create([
            'menu_group_id' => $menuGroup->id,
            'menu_category_id' => $category->id,
            'name' => 'Nasi Box Ayam',
            'description' => 'Menu ayam untuk form edit.',
            'base_price' => 35000,
            'is_default' => true,
        ]);
        $image = MenuImage::factory()->create([
            'menu_item_id' => $menu->id,
            'image_url' => 'https://example.com/images/nasi-box-ayam-edit.jpg',
            'is_primary' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('menu.edit', $menuGroup))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/menu/edit-menu')
                ->where('grup.id', $menuGroup->id)
                ->where('grup.name', 'Nasi Box')
                ->where('grup.menu_category_id', $category->id)
                ->has('varianList', 1)
                ->where('varianList.0.id', $menu->id)
                ->where('varianList.0.menu_category_id', $category->id)
                ->where('varianList.0.name', 'Nasi Box Ayam')
                ->where('varianList.0.description', 'Menu ayam untuk form edit.')
                ->where('varianList.0.base_price', '35000.00')
                ->where('varianList.0.image_url', 'https://example.com/images/nasi-box-ayam-edit.jpg')
                ->has('menuGroups', 1)
                ->where('menuGroups.0.id', $menuGroup->id)
                ->where('menuGroups.0.menu_category_id', $category->id)
                ->where('menuGroups.0.name', 'Nasi Box')
                ->has('menuCategories', 1)
                ->where('menuCategories.0.id', $category->id)
                ->where('menuCategories.0.name', 'Paket Box'));
    }

    public function test_edit_menu_route_redirects_to_group_edit_when_given_a_menu_item_id(): void
    {
        $user = User::factory()->create();
        $menuGroup = MenuGroup::factory()->create();
        $menu = MenuItem::factory()->create([
            'id' => 99,
            'menu_group_id' => $menuGroup->id,
        ]);

        $this->actingAs($user)
            ->get(route('menu.edit', $menu))
            ->assertRedirect(route('menu.edit', $menuGroup));
    }

    public function test_edit_single_menu_item_page_uses_the_same_edit_page(): void
    {
        $user = User::factory()->create();
        $category = MenuCategory::factory()->create([
            'name' => 'Dessert',
        ]);
        $menu = MenuItem::factory()->create([
            'menu_group_id' => null,
            'menu_category_id' => $category->id,
            'name' => 'Brownies Single',
            'description' => 'Menu tanpa grup.',
            'base_price' => 28000,
            'is_default' => true,
        ]);

        $this->actingAs($user)
            ->get(route('menu.edit', ['menu' => $menu->id, 'type' => 'item']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/menu/edit-menu')
                ->where('grup', null)
                ->where('menuType', 'item')
                ->where('menuId', $menu->id)
                ->has('varianList', 1)
                ->where('varianList.0.id', $menu->id)
                ->where('varianList.0.menu_category_id', $category->id)
                ->where('varianList.0.name', 'Brownies Single')
                ->where('varianList.0.description', 'Menu tanpa grup.')
                ->where('varianList.0.base_price', '28000.00'));
    }

    public function test_edit_single_menu_item_page_can_be_resolved_without_type_query(): void
    {
        $user = User::factory()->create();
        $menu = MenuItem::factory()->create([
            'menu_group_id' => null,
            'name' => 'Dimsum Single',
            'description' => 'Tetap terbuka tanpa query type.',
            'base_price' => 24000,
            'is_default' => true,
        ]);

        $this->actingAs($user)
            ->get(route('menu.edit', $menu))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/menu/edit-menu')
                ->where('grup', null)
                ->where('menuType', 'item')
                ->where('menuId', $menu->id)
                ->has('varianList', 1)
                ->where('varianList.0.id', $menu->id)
                ->where('varianList.0.menu_category_id', null)
                ->where('varianList.0.name', 'Dimsum Single'));
    }

    public function test_edit_menu_page_keeps_inactive_group_and_category_options_available(): void
    {
        $user = User::factory()->create();
        $activeCategory = MenuCategory::factory()->create([
            'name' => 'Paket Aktif',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $inactiveCategory = MenuCategory::factory()->create([
            'name' => 'Paket Arsip',
            'is_active' => false,
            'sort_order' => 2,
        ]);
        $menuGroup = MenuGroup::factory()->create([
            'menu_category_id' => $activeCategory->id,
            'name' => 'Nasi Box',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $inactiveGroup = MenuGroup::factory()->create([
            'menu_category_id' => $inactiveCategory->id,
            'name' => 'Snack Lama',
            'is_active' => false,
            'sort_order' => 2,
        ]);
        MenuItem::factory()->create([
            'menu_group_id' => $menuGroup->id,
            'name' => 'Nasi Box Ayam',
            'is_default' => true,
        ]);

        $this->actingAs($user)
            ->get(route('menu.edit', $menuGroup))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/menu/edit-menu')
                ->has('menuGroups', 2)
                ->where('menuGroups.1.id', $inactiveGroup->id)
                ->where('menuGroups.1.name', 'Snack Lama')
                ->where('menuGroups.1.menu_category_id', $inactiveCategory->id)
                ->has('menuCategories', 2)
                ->where('menuCategories.1.id', $inactiveCategory->id)
                ->where('menuCategories.1.name', 'Paket Arsip'));
    }
}
