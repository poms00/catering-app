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

class MenuIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_index_page_displays_grouped_menu_cards(): void
    {
        $user = User::factory()->create();
        $category = MenuCategory::factory()->create([
            'name' => 'Paket Nasi',
        ]);
        $menuGroup = MenuGroup::factory()->create([
            'menu_category_id' => $category->id,
            'name' => 'Nasi Box',
        ]);

        $defaultVariant = MenuItem::factory()->create([
            'menu_group_id' => $menuGroup->id,
            'name' => 'Nasi Box Ayam',
            'base_price' => 35000,
            'description' => 'Varian ayam goreng.',
            'is_default' => true,
        ]);

        $secondaryVariant = MenuItem::factory()->create([
            'menu_group_id' => $menuGroup->id,
            'name' => 'Nasi Box Rendang',
            'base_price' => 42000,
            'description' => 'Varian rendang.',
            'is_default' => false,
        ]);

        $menuImage = MenuImage::factory()->create([
            'menu_item_id' => $defaultVariant->id,
            'image_url' => 'https://example.com/images/nasi-box-ayam.jpg',
            'is_primary' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($user)
            ->withCookie('sidebar_state', 'false')
            ->get(route('menu.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/menu/index')
                ->where('sidebarOpen', false)
                ->has('menuItems', 2)
                ->where('menuItems.0.id', $defaultVariant->id)
                ->where('menuItems.0.menu_group.id', $menuGroup->id)
                ->where('menuItems.0.menu_group.name', 'Nasi Box')
                ->where('menuItems.0.menu_category.id', $category->id)
                ->where('menuItems.0.menu_category.name', 'Paket Nasi')
                ->where('menuItems.0.primary_image', 'https://example.com/images/nasi-box-ayam.jpg')
                ->where('menuItems.1.id', $secondaryVariant->id)
                ->has('menuCategories', 1)
                ->where('menuCategories.0.id', $category->id)
                ->where('menuCategories.0.groups.0.id', $menuGroup->id)
                ->has('menuGroups', 1)
                ->where('menuGroups.0.id', $menuGroup->id)
                ->where('menuGroups.0.name', 'Nasi Box'));
    }

    public function test_menu_index_page_places_the_default_variant_first_within_a_group(): void
    {
        $user = User::factory()->create();
        $menuGroup = MenuGroup::factory()->create([
            'name' => 'Paket Bento',
        ]);

        $nonDefaultVariant = MenuItem::factory()->create([
            'menu_group_id' => $menuGroup->id,
            'name' => 'Bento Reguler',
            'sort_order' => 1,
            'is_default' => false,
        ]);

        $defaultVariant = MenuItem::factory()->create([
            'menu_group_id' => $menuGroup->id,
            'name' => 'Bento Premium',
            'sort_order' => 2,
            'is_default' => true,
        ]);

        $this->actingAs($user)
            ->get(route('menu.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/menu/index')
                ->has('menuItems', 2)
                ->where('menuItems.0.id', $defaultVariant->id)
                ->where('menuItems.0.is_default', true)
                ->where('menuItems.1.id', $nonDefaultVariant->id)
                ->where('menuItems.1.is_default', false));
    }

    public function test_menu_index_page_renders_items_from_inactive_groups_without_group_metadata(): void
    {
        $user = User::factory()->create();
        $category = MenuCategory::factory()->create([
            'name' => 'Snack Box',
        ]);
        $inactiveGroup = MenuGroup::factory()->create([
            'menu_category_id' => $category->id,
            'name' => 'Snack Corporate',
            'is_active' => false,
        ]);
        $item = MenuItem::factory()->create([
            'menu_group_id' => $inactiveGroup->id,
            'name' => 'Snack Aktif',
            'is_active' => true,
            'is_default' => true,
        ]);

        $this->actingAs($user)
            ->get(route('menu.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/menu/index')
                ->has('menuItems', 1)
                ->where('menuItems.0.id', $item->id)
                ->where('menuItems.0.menu_group_id', $inactiveGroup->id)
                ->where('menuItems.0.menu_group', null)
                ->where('menuItems.0.menu_category', null));
    }

    public function test_menu_index_page_includes_inactive_empty_groups_in_admin_props(): void
    {
        $user = User::factory()->create();
        $category = MenuCategory::factory()->create([
            'name' => 'Kategori Lama',
            'is_active' => true,
        ]);
        $inactiveEmptyGroup = MenuGroup::factory()->create([
            'menu_category_id' => $category->id,
            'name' => 'Grup Kosong',
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->get(route('menu.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/menu/index')
                ->has('menuGroups', 1)
                ->where('menuGroups.0.id', $inactiveEmptyGroup->id)
                ->where('menuGroups.0.name', 'Grup Kosong')
                ->where('menuGroups.0.is_active', false)
                ->where('menuGroups.0.items_count', 0));
    }
}
