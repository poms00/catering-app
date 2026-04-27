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
                ->where('menuItems.0.menu_images', 'https://example.com/images/nasi-box-ayam.jpg')
                ->where('menuItems.0.images.0.id', $menuImage->id)
                ->where('menuItems.1.id', $secondaryVariant->id)
                ->has('menuCategories', 1)
                ->where('menuCategories.0.id', $category->id)
                ->where('menuCategories.0.groups.0.id', $menuGroup->id)
                ->has('menuGroups', 1)
                ->where('menuGroups.0.id', $menuGroup->id)
                ->where('menuGroups.0.name', 'Nasi Box'));
    }
}
