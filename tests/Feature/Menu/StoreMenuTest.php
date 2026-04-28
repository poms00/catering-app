<?php

namespace Tests\Feature\Menu;

use App\Models\MenuCategory;
use App\Models\MenuGroup;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_store_a_menu_group_with_variants(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $category = MenuCategory::factory()->create([
            'name' => 'Paket Catering',
        ]);

        $response = $this->actingAs($user)->post(route('menu.store'), [
            'creates_with_group' => '1',
            'menu_category_id' => $category->id,
            'name' => 'Nasi Box Baru',
            'description' => 'Paket nasi box untuk kebutuhan catering harian.',
            'sort_order' => 3,
            'is_active' => '1',
            'variants' => [
                [
                    'name' => 'Nasi Box Ayam',
                    'base_price' => 38000,
                    'description' => 'Varian ayam goreng.',
                    'image' => UploadedFile::fake()->image('nasi-box-ayam.jpg'),
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
            ->assertRedirect(route('menu.index'))
            ->assertSessionHas('success', 'Grup "Nasi Box Baru" berhasil dibuat.');

        $this->assertDatabaseHas('menu_groups', [
            'id' => $group->id,
            'menu_category_id' => $category->id,
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
        $this->assertDatabaseHas('menu_images', [
            'menu_item_id' => MenuItem::query()->where('name', 'Nasi Box Ayam')->value('id'),
            'is_primary' => true,
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

    public function test_authenticated_user_can_store_mixed_wrapper_and_single_entries_from_builder(): void
    {
        $user = User::factory()->create();
        $wrapperCategory = MenuCategory::factory()->create([
            'name' => 'Paket Hemat',
        ]);
        $singleCategory = MenuCategory::factory()->create([
            'name' => 'Minuman',
        ]);

        $response = $this->actingAs($user)->post(route('menu.store'), [
            'entries' => [
                [
                    'type' => 'wrapper',
                    'name' => 'Paket Hemat',
                    'menu_category_id' => $wrapperCategory->id,
                    'sort_order' => 1,
                    'is_active' => '1',
                    'variants' => [
                        [
                            'name' => 'Paket Hemat Ayam',
                            'menu_category_id' => $wrapperCategory->id,
                            'base_price' => 32000,
                            'description' => 'Pilihan ayam goreng.',
                            'sort_order' => 1,
                            'is_active' => '1',
                        ],
                        [
                            'name' => 'Paket Hemat Ikan',
                            'menu_category_id' => $wrapperCategory->id,
                            'base_price' => 34000,
                            'description' => 'Pilihan ikan fillet.',
                            'sort_order' => 2,
                            'is_active' => '1',
                        ],
                    ],
                ],
                [
                    'type' => 'single',
                    'sort_order' => 2,
                    'variants' => [
                        [
                            'name' => 'Es Teh Jumbo',
                            'menu_category_id' => $singleCategory->id,
                            'base_price' => 8000,
                            'description' => 'Menu di luar wrapper.',
                            'sort_order' => 1,
                            'is_active' => '1',
                        ],
                    ],
                ],
            ],
        ]);

        $wrapper = MenuGroup::query()->firstOrFail();

        $response
            ->assertRedirect(route('menu.index'))
            ->assertSessionHas('success', '2 entri menu berhasil dibuat.');

        $this->assertDatabaseHas('menu_groups', [
            'id' => $wrapper->id,
            'menu_category_id' => $wrapperCategory->id,
            'name' => 'Paket Hemat',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('menu_items', [
            'menu_group_id' => $wrapper->id,
            'menu_category_id' => $wrapperCategory->id,
            'name' => 'Paket Hemat Ayam',
            'base_price' => '32000.00',
            'sort_order' => 1,
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('menu_items', [
            'menu_group_id' => $wrapper->id,
            'menu_category_id' => $wrapperCategory->id,
            'name' => 'Paket Hemat Ikan',
            'base_price' => '34000.00',
            'sort_order' => 2,
            'is_default' => false,
        ]);

        $this->assertDatabaseHas('menu_items', [
            'menu_group_id' => null,
            'menu_category_id' => $singleCategory->id,
            'name' => 'Es Teh Jumbo',
            'base_price' => '8000.00',
            'description' => 'Menu di luar wrapper.',
            'sort_order' => 1,
            'is_default' => true,
        ]);
    }

    public function test_authenticated_user_can_store_builder_entries_with_new_category_drafts(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('menu.store'), [
            'category_drafts' => [
                [
                    'temp_id' => -1,
                    'name' => 'Minuman Signature',
                ],
                [
                    'temp_id' => -2,
                    'name' => 'Favorit Dingin',
                ],
            ],
            'entries' => [
                [
                    'type' => 'single',
                    'sort_order' => 1,
                    'variants' => [
                        [
                            'name' => 'Es Kopi Aren',
                            'menu_category_id' => -1,
                            'menu_category_ids' => [-1, -2],
                            'base_price' => 18000,
                            'sort_order' => 1,
                            'is_active' => '1',
                        ],
                    ],
                ],
            ],
        ]);

        $category = MenuCategory::query()
            ->where('name', 'Minuman Signature')
            ->firstOrFail();
        $secondCategory = MenuCategory::query()
            ->where('name', 'Favorit Dingin')
            ->firstOrFail();

        $response
            ->assertRedirect(route('menu.index'))
            ->assertSessionHas('success', '1 entri menu berhasil dibuat.');

        $this->assertDatabaseHas('menu_categories', [
            'id' => $category->id,
            'name' => 'Minuman Signature',
            'slug' => 'minuman-signature',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('menu_items', [
            'menu_group_id' => null,
            'menu_category_id' => $category->id,
            'name' => 'Es Kopi Aren',
            'base_price' => '18000.00',
            'is_default' => true,
        ]);
        $this->assertDatabaseHas('menu_category_menu_item', [
            'menu_category_id' => $category->id,
            'menu_item_id' => MenuItem::query()->where('name', 'Es Kopi Aren')->value('id'),
        ]);
        $this->assertDatabaseHas('menu_category_menu_item', [
            'menu_category_id' => $secondCategory->id,
            'menu_item_id' => MenuItem::query()->where('name', 'Es Kopi Aren')->value('id'),
        ]);
    }

    public function test_store_menu_uses_sort_order_one_as_the_default_variant(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('menu.store'), [
            'creates_with_group' => '1',
            'name' => 'Paket Sarapan',
            'variants' => [
                [
                    'name' => 'Sarapan Reguler',
                    'base_price' => 20000,
                    'sort_order' => 1,
                    'is_default' => '0',
                    'is_active' => '1',
                ],
                [
                    'name' => 'Sarapan Premium',
                    'base_price' => 25000,
                    'sort_order' => 2,
                    'is_default' => '1',
                    'is_active' => '1',
                ],
            ],
        ]);

        $group = MenuGroup::query()->firstOrFail();

        $this->assertDatabaseHas('menu_items', [
            'menu_group_id' => $group->id,
            'name' => 'Sarapan Reguler',
            'sort_order' => 1,
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('menu_items', [
            'menu_group_id' => $group->id,
            'name' => 'Sarapan Premium',
            'sort_order' => 2,
            'is_default' => false,
        ]);
    }

    public function test_authenticated_user_can_store_menu_variants_into_an_existing_group(): void
    {
        $user = User::factory()->create();
        $group = MenuGroup::factory()->create([
            'name' => 'Paket Bento',
        ]);

        $response = $this->actingAs($user)->post(route('menu.store'), [
            'creates_with_group' => '0',
            'menu_group_id' => $group->id,
            'variants' => [
                [
                    'name' => 'Bento Ayam Teriyaki',
                    'base_price' => 45000,
                    'description' => 'Varian ayam dengan saus teriyaki.',
                    'sort_order' => 1,
                    'is_default' => '1',
                    'is_active' => '1',
                ],
            ],
        ]);

        $response
            ->assertRedirect(route('menu.index'))
            ->assertSessionHas(
                'success',
                'Menu berhasil ditambahkan ke grup "Paket Bento".'
            );

        $this->assertDatabaseHas('menu_items', [
            'menu_group_id' => $group->id,
            'name' => 'Bento Ayam Teriyaki',
            'base_price' => '45000.00',
            'description' => 'Varian ayam dengan saus teriyaki.',
            'sort_order' => 1,
            'is_default' => true,
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
            ->assertRedirect(route('menu.index'))
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

    public function test_store_menu_with_non_array_variants_returns_validation_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('menu.create'))
            ->post(route('menu.store'), [
                'creates_with_group' => '0',
                'variants' => 'not-an-array',
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
