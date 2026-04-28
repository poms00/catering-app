<?php

namespace Tests\Feature\Menu;

use App\Models\MenuCategory;
use App\Models\MenuGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MenuCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_create_page_displays_menu_group_options(): void
    {
        $user = User::factory()->create();
        $category = MenuCategory::factory()->create([
            'name' => 'Paket Nasi',
        ]);
        $menuGroup = MenuGroup::factory()->create([
            'menu_category_id' => $category->id,
            'name' => 'Nasi Tumpeng',
            'is_active' => true,
        ]);
        $this->actingAs($user)
            ->get(route('menu.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/menu/create-menu')
                ->where('grup', null)
                ->where('menuType', 'create')
                ->where('varianList', [])
                ->has('menuGroups', 1)
                ->where('menuGroups.0.id', $menuGroup->id)
                ->where('menuGroups.0.name', 'Nasi Tumpeng')
                ->where('menuGroups.0.menu_category_id', $category->id)
                ->has('menuCategories', 1)
                ->where('menuCategories.0.id', $category->id)
                ->where('menuCategories.0.name', 'Paket Nasi'));
    }
}
