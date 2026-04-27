<?php

namespace Tests\Feature\Menu;

use App\Models\MenuGroup;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_store_a_menu_group_with_variants(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('menu.store'), [
            'creates_with_group' => '1',
            'name' => 'Nasi Box Baru',
            'description' => 'Paket nasi box untuk kebutuhan catering harian.',
            'sort_order' => 3,
            'is_active' => '1',
            'variants' => [
                [
                    'name' => 'Nasi Box Ayam',
                    'base_price' => 38000,
                    'description' => 'Varian ayam goreng.',
                    'sort_order' => 1,
                    'is_default' => '1',
                    'is_active' => '1',
                ],
                [
                    'name' => 'Nasi Box Rendang',
                    'base_price' => 42000,
                    'description' => 'Varian rendang.',
                    'sort_order' => 2,
                    'is_default' => '0',
                    'is_active' => '1',
                ],
            ],
        ]);

        $group = MenuGroup::query()->firstOrFail();

        $response
            ->assertRedirect(route('menu.show', $group))
            ->assertSessionHas('success', 'Grup "Nasi Box Baru" berhasil dibuat.');

        $this->assertDatabaseHas('menu_groups', [
            'id' => $group->id,
            'name' => 'Nasi Box Baru',
            'description' => 'Paket nasi box untuk kebutuhan catering harian.',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('menu_items', [
            'menu_group_id' => $group->id,
            'name' => 'Nasi Box Ayam',
            'base_price' => '38000.00',
            'description' => 'Varian ayam goreng.',
            'sort_order' => 1,
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('menu_items', [
            'menu_group_id' => $group->id,
            'name' => 'Nasi Box Rendang',
            'base_price' => '42000.00',
            'description' => 'Varian rendang.',
            'sort_order' => 2,
            'is_default' => false,
            'is_active' => true,
        ]);
    }

    public function test_store_menu_requires_at_least_one_variant(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('menu.create'))
            ->post(route('menu.store'), [
                'creates_with_group' => '1',
                'name' => 'Nasi Box Baru',
                'description' => 'Paket nasi box untuk kebutuhan catering harian.',
                'variants' => [],
            ]);

        $response
            ->assertRedirect(route('menu.create'))
            ->assertSessionHasErrors([
                'variants',
            ]);
    }

    public function test_store_menu_without_group_redirects_to_single_item_detail_when_only_one_variant_exists(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('menu.store'), [
            'creates_with_group' => '0',
            'variants' => [
                [
                    'name' => 'Brownies Box Original',
                    'base_price' => 28000,
                    'description' => 'Brownies cokelat tanpa varian grup.',
                    'sort_order' => 1,
                    'is_active' => '1',
                ],
            ],
        ]);

        $item = MenuItem::query()->firstOrFail();

        $response
            ->assertRedirect(route('menu.show', ['menu' => $item, 'type' => 'item']))
            ->assertSessionHas('success', 'Menu "Brownies Box Original" berhasil dibuat.');

        $this->assertDatabaseHas('menu_items', [
            'id' => $item->id,
            'menu_group_id' => null,
            'name' => 'Brownies Box Original',
            'base_price' => '28000.00',
            'description' => 'Brownies cokelat tanpa varian grup.',
            'is_default' => true,
        ]);
    }

    public function test_store_menu_without_group_rejects_multiple_variants(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('menu.create'))
            ->post(route('menu.store'), [
                'creates_with_group' => '0',
                'variants' => [
                    [
                        'name' => 'Varian Satu',
                        'base_price' => 10000,
                        'is_active' => '1',
                    ],
                    [
                        'name' => 'Varian Dua',
                        'base_price' => 12000,
                        'is_active' => '1',
                    ],
                ],
            ]);

        $response
            ->assertRedirect(route('menu.create'))
            ->assertSessionHasErrors([
                'variants',
            ]);

        $this->assertDatabaseCount('menu_items', 0);
        $this->assertDatabaseCount('menu_groups', 0);
    }
}
