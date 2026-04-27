<?php

namespace Tests\Feature\Menu;

use App\Models\MenuImage;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShowSingleMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_variant_group_detail_page_contains_one_menu_item(): void
    {
        $user = User::factory()->create();
        $menu = MenuItem::factory()->create([
            'menu_group_id' => null,
            'name' => 'Brownies Box Original',
            'description' => 'Brownies cokelat tanpa varian grup.',
            'base_price' => 28000,
            'is_default' => true,
        ]);
        $image = MenuImage::factory()->create([
            'menu_item_id' => $menu->id,
            'image_url' => 'https://example.com/images/brownies-box.jpg',
            'is_primary' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('menu.show', ['menu' => $menu->id, 'type' => 'item']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/menu/show-detail-menu')
                ->where('group.id', $menu->id)
                ->where('group.name', 'Brownies Box Original')
                ->where('canEdit', false)
                ->has('group.menu_items', 1)
                ->where('group.menu_items.0.id', $menu->id)
                ->where('group.menu_items.0.name', 'Brownies Box Original')
                ->has('group.menu_items.0.images', 1)
                ->where('group.menu_items.0.images.0.id', $image->id)
                ->where('group.menu_items.0.images.0.image_url', 'https://example.com/images/brownies-box.jpg'));
    }
}
