<?php

namespace Tests\Feature\Menu;

use App\Models\MenuCategory;
use App\Models\MenuGroup;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_a_menu_item(): void
    {
        $user = User::factory()->create();
        $menuGroup = MenuGroup::factory()->create();
        $newCategory = MenuCategory::factory()->create();
        $newMenuGroup = MenuGroup::factory()->create([
            'menu_category_id' => $newCategory->id,
        ]);
        $menu = MenuItem::factory()->create([
            'menu_group_id' => $menuGroup->id,
            'name' => 'Menu Lama',
            'description' => 'Deskripsi lama.',
            'base_price' => 25000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->put(route('menu.update', $menu), [
            'requires_group' => '1',
            'menu_group_id' => $newMenuGroup->id,
            'name' => 'Menu Baru',
            'description' => 'Deskripsi baru untuk menu.',
            'base_price' => 42000,
            'is_active' => '1',
        ]);

        $response
            ->assertRedirect(route('menu.show', $menu))
            ->assertSessionHas('status', 'menu-updated');

        $this->assertDatabaseHas('menu_items', [
            'id' => $menu->id,
            'menu_group_id' => $newMenuGroup->id,
            'name' => 'Menu Baru',
            'description' => 'Deskripsi baru untuk menu.',
            'base_price' => '42000.00',
            'is_default' => false,
            'is_active' => true,
        ]);
    }

    public function test_update_menu_requires_valid_payload(): void
    {
        $user = User::factory()->create();
        $menu = MenuItem::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('menu.edit', $menu))
            ->put(route('menu.update', $menu), [
                'requires_group' => '1',
                'menu_group_id' => 999999,
                'name' => '',
                'description' => '',
                'base_price' => -100,
            ]);

        $response
            ->assertRedirect(route('menu.edit', $menu))
            ->assertSessionHasErrors([
                'menu_group_id',
                'name',
                'description',
                'base_price',
            ]);
    }

    public function test_update_menu_can_remove_group_when_group_is_not_required(): void
    {
        $user = User::factory()->create();
        $menuGroup = MenuGroup::factory()->create();
        $menu = MenuItem::factory()->create([
            'menu_group_id' => $menuGroup->id,
        ]);

        $response = $this->actingAs($user)->put(route('menu.update', $menu), [
            'requires_group' => '0',
            'name' => 'Menu Tanpa Grup',
            'description' => 'Deskripsi tanpa grup.',
            'base_price' => 18000,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('menu.show', $menu));

        $this->assertDatabaseHas('menu_items', [
            'id' => $menu->id,
            'menu_group_id' => null,
            'name' => 'Menu Tanpa Grup',
        ]);
    }
}
