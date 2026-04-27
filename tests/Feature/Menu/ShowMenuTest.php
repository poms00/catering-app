<?php

namespace Tests\Feature\Menu;

use App\Models\MenuGroup;
use App\Models\MenuImage;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShowMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_detail_page_displays_menu_group_variants_with_images(): void
    {
        $user = User::factory()->create();
        $menuGroup = MenuGroup::factory()->create([
            'name' => 'Nasi Goreng',
        ]);

        $defaultVariant = MenuItem::factory()->create([
            'menu_group_id' => $menuGroup->id,
            'name' => 'Nasi Box Premium',
            'base_price' => 45000,
            'description' => 'Paket nasi box lengkap untuk acara kantor.',
            'is_default' => true,
        ]);

        MenuImage::factory()->create([
            'menu_item_id' => $defaultVariant->id,
            'image_url' => 'https://example.com/images/menu-secondary.jpg',
            'is_primary' => false,
            'sort_order' => 2,
        ]);

        $primaryImage = MenuImage::factory()->create([
            'menu_item_id' => $defaultVariant->id,
            'image_url' => 'https://example.com/images/menu-primary.jpg',
            'is_primary' => true,
            'sort_order' => 1,
        ]);

        $secondaryVariant = MenuItem::factory()->create([
            'menu_group_id' => $menuGroup->id,
            'name' => 'Nasi Box Seafood',
            'base_price' => 55000,
            'description' => 'Varian seafood untuk tamu VIP.',
            'is_default' => false,
        ]);

        $secondaryVariantImage = MenuImage::factory()->create([
            'menu_item_id' => $secondaryVariant->id,
            'image_url' => 'https://example.com/images/menu-seafood.jpg',
            'is_primary' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('menu.show', $menuGroup))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/menu/show-detail-menu')
                ->where('canEdit', true)
                ->where('group.id', $menuGroup->id)
                ->where('group.name', 'Nasi Goreng')
                ->has('group.menu_items', 2)
                ->where('group.menu_items.0.id', $defaultVariant->id)
                ->where('group.menu_items.0.name', 'Nasi Box Premium')
                ->where('group.menu_items.0.is_default', true)
                ->has('group.menu_items.0.images', 2)
                ->where('group.menu_items.0.images.0.id', $primaryImage->id)
                ->where('group.menu_items.0.images.0.image_url', 'https://example.com/images/menu-primary.jpg')
                ->where('group.menu_items.1.id', $secondaryVariant->id)
                ->where('group.menu_items.1.name', 'Nasi Box Seafood')
                ->where('group.menu_items.1.images.0.id', $secondaryVariantImage->id),
            );
    }

    public function test_menu_detail_page_loads_group_images_and_category_for_the_show_page(): void
    {
        $user = User::factory()->create();
        $menuGroup = MenuGroup::factory()->create([
            'name' => 'Paket Prasmanan',
        ]);

        $defaultVariant = MenuItem::factory()->create([
            'menu_group_id' => $menuGroup->id,
            'name' => 'Prasmanan Reguler',
            'base_price' => 75000,
            'description' => 'Menu default untuk paket prasmanan.',
            'is_default' => true,
        ]);

        $groupImage = MenuImage::factory()->create([
            'menu_group_id' => $menuGroup->id,
            'menu_item_id' => null,
            'image_url' => 'https://example.com/images/prasmanan-group.jpg',
            'is_primary' => true,
            'sort_order' => 1,
        ]);

        MenuImage::factory()->create([
            'menu_item_id' => $defaultVariant->id,
            'image_url' => 'https://example.com/images/prasmanan-reguler.jpg',
            'is_primary' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('menu.show', $menuGroup))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/menu/show-detail-menu')
                ->where('canEdit', true)
                ->where('group.id', $menuGroup->id)
                ->where('group.menu_category.id', $menuGroup->menu_category_id)
                ->has('group.images', 1)
                ->where('group.images.0.id', $groupImage->id)
                ->where('group.images.0.menu_group_id', $menuGroup->id)
                ->where('group.images.0.image_url', 'https://example.com/images/prasmanan-group.jpg')
                ->has('group.menu_items', 1)
                ->where('group.menu_items.0.id', $defaultVariant->id)
                ->where('group.menu_items.0.is_default', true),
            );
    }
}
