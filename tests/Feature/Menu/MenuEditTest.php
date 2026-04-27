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
            'name' => 'Nasi Box Ayam',
            'description' => 'Menu ayam untuk form edit.',
            'is_default' => true,
        ]);
        $image = MenuImage::factory()->create([
            'menu_item_id' => $menu->id,
            'image_url' => 'https://example.com/images/nasi-box-ayam-edit.jpg',
            'is_primary' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('menu.edit', $menu))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/menu/edit-menu')
                ->where('menu.id', $menu->id)
                ->where('menu.menu_group.id', $menuGroup->id)
                ->where('menu.menu_group.name', 'Nasi Box')
                ->where('menu.menu_category.id', $category->id)
                ->where('menu.menu_category.name', 'Paket Box')
                ->missing('menu.menu_group.menu_items')
                ->has('menu.images', 1)
                ->where('menu.images.0.id', $image->id)
                ->where('menu.images.0.url', 'https://example.com/images/nasi-box-ayam-edit.jpg')
                ->has('menuGroups', 1)
                ->where('menuGroups.0.id', $menuGroup->id)
                ->where('menuGroups.0.menu_category_id', $category->id)
                ->where('menuGroups.0.name', 'Nasi Box'));
    }
}
