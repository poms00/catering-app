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

class UpdateMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_a_menu_group_and_sync_variants(): void
    {
        $user = User::factory()->create();
        $oldCategory = MenuCategory::factory()->create();
        $newCategory = MenuCategory::factory()->create([
            'name' => 'Paket Premium',
        ]);
        $itemCategory = MenuCategory::factory()->create([
            'name' => 'Menu Premium',
        ]);
        $group = MenuGroup::factory()->create([
            'menu_category_id' => $oldCategory->id,
            'name' => 'Paket Lama',
            'description' => 'Deskripsi lama.',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $updatedVariant = MenuItem::factory()->create([
            'menu_group_id' => $group->id,
            'name' => 'Varian Lama',
            'description' => 'Deskripsi varian lama.',
            'base_price' => 25000,
            'sort_order' => 1,
            'is_default' => true,
            'is_active' => true,
        ]);
        $deletedVariant = MenuItem::factory()->create([
            'menu_group_id' => $group->id,
            'name' => 'Varian Hapus',
            'sort_order' => 2,
            'is_default' => false,
        ]);

        $response = $this->actingAs($user)->put(route('menu.update', $group), [
            'menu_category_id' => $newCategory->id,
            'name' => 'Paket Baru',
            'description' => 'Deskripsi baru.',
            'sort_order' => 4,
            'is_active' => '0',
            'variants' => [
                [
                    'id' => $updatedVariant->id,
                    'menu_category_id' => $itemCategory->id,
                    'name' => 'Varian Update',
                    'description' => 'Deskripsi varian baru.',
                    'base_price' => 42000,
                    'sort_order' => 1,
                    'is_default' => '0',
                    'is_active' => '1',
                ],
                [
                    'menu_category_id' => $itemCategory->id,
                    'name' => 'Varian Tambahan',
                    'description' => 'Varian baru ditambahkan.',
                    'base_price' => 47000,
                    'sort_order' => 2,
                    'is_default' => '1',
                    'is_active' => '1',
                ],
            ],
        ]);

        $response
            ->assertRedirect(route('menu.show', $group))
            ->assertSessionHas('success', 'Grup "Paket Baru" berhasil diperbarui.');

        $this->assertDatabaseHas('menu_groups', [
            'id' => $group->id,
            'menu_category_id' => $newCategory->id,
            'name' => 'Paket Baru',
            'description' => 'Deskripsi baru.',
            'sort_order' => 4,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('menu_items', [
            'id' => $updatedVariant->id,
            'menu_group_id' => $group->id,
            'menu_category_id' => $itemCategory->id,
            'name' => 'Varian Update',
            'description' => 'Deskripsi varian baru.',
            'base_price' => '42000.00',
            'sort_order' => 1,
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('menu_items', [
            'menu_group_id' => $group->id,
            'menu_category_id' => $itemCategory->id,
            'name' => 'Varian Tambahan',
            'description' => 'Varian baru ditambahkan.',
            'base_price' => '47000.00',
            'sort_order' => 2,
            'is_default' => false,
            'is_active' => true,
        ]);

        $this->assertDatabaseMissing('menu_items', [
            'id' => $deletedVariant->id,
        ]);
    }

    public function test_update_menu_uses_sort_order_one_as_the_default_variant(): void
    {
        $user = User::factory()->create();
        $group = MenuGroup::factory()->create();
        $firstVariant = MenuItem::factory()->create([
            'menu_group_id' => $group->id,
            'name' => 'Varian Pertama',
            'sort_order' => 1,
            'is_default' => false,
        ]);
        $secondVariant = MenuItem::factory()->create([
            'menu_group_id' => $group->id,
            'name' => 'Varian Kedua',
            'sort_order' => 2,
            'is_default' => true,
        ]);

        $this->actingAs($user)->put(route('menu.update', $group), [
            'name' => $group->name,
            'variants' => [
                [
                    'id' => $firstVariant->id,
                    'name' => 'Varian Pertama',
                    'base_price' => 30000,
                    'sort_order' => 1,
                    'is_default' => '0',
                    'is_active' => '1',
                ],
                [
                    'id' => $secondVariant->id,
                    'name' => 'Varian Kedua',
                    'base_price' => 35000,
                    'sort_order' => 2,
                    'is_default' => '1',
                    'is_active' => '1',
                ],
            ],
        ]);

        $this->assertDatabaseHas('menu_items', [
            'id' => $firstVariant->id,
            'sort_order' => 1,
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('menu_items', [
            'id' => $secondVariant->id,
            'sort_order' => 2,
            'is_default' => false,
        ]);
    }

    public function test_authenticated_user_can_update_a_menu_with_new_category_draft(): void
    {
        $user = User::factory()->create();
        $group = MenuGroup::factory()->create([
            'name' => 'Paket Lama',
        ]);
        $variant = MenuItem::factory()->create([
            'menu_group_id' => $group->id,
            'name' => 'Varian Lama',
            'sort_order' => 1,
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)->put(route('menu.update', $group), [
            'category_drafts' => [
                [
                    'temp_id' => -1,
                    'name' => 'Kategori Baru Edit',
                ],
                [
                    'temp_id' => -2,
                    'name' => 'Tag Tambahan Edit',
                ],
            ],
            'name' => $group->name,
            'variants' => [
                [
                    'id' => $variant->id,
                    'menu_category_id' => -1,
                    'menu_category_ids' => [-1, -2],
                    'name' => 'Varian Lama',
                    'base_price' => 30000,
                    'sort_order' => 1,
                    'is_active' => '1',
                ],
            ],
        ]);

        $category = MenuCategory::query()
            ->where('name', 'Kategori Baru Edit')
            ->firstOrFail();
        $secondCategory = MenuCategory::query()
            ->where('name', 'Tag Tambahan Edit')
            ->firstOrFail();

        $response
            ->assertRedirect(route('menu.show', $group))
            ->assertSessionHas('success', 'Grup "Paket Lama" berhasil diperbarui.');

        $this->assertDatabaseHas('menu_items', [
            'id' => $variant->id,
            'menu_category_id' => $category->id,
            'name' => 'Varian Lama',
        ]);
        $this->assertDatabaseHas('menu_category_menu_item', [
            'menu_category_id' => $category->id,
            'menu_item_id' => $variant->id,
        ]);
        $this->assertDatabaseHas('menu_category_menu_item', [
            'menu_category_id' => $secondCategory->id,
            'menu_item_id' => $variant->id,
        ]);
    }

    public function test_update_menu_requires_valid_payload(): void
    {
        $user = User::factory()->create();
        $group = MenuGroup::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('menu.edit', $group))
            ->put(route('menu.update', $group), [
                'menu_category_id' => 999999,
                'name' => '',
                'description' => '',
                'sort_order' => -1,
                'variants' => [],
            ]);

        $response
            ->assertRedirect(route('menu.edit', $group))
            ->assertSessionHasErrors([
                'menu_category_id',
                'name',
                'sort_order',
                'variants',
            ]);
    }

    public function test_authenticated_user_can_update_a_single_menu_item_from_the_same_edit_page(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $item = MenuItem::factory()->create([
            'menu_group_id' => null,
            'name' => 'Brownies Lama',
            'description' => 'Deskripsi lama.',
            'base_price' => 22000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->put(
            route('menu.update', ['menu' => $item->id, 'type' => 'item']),
            [
                'name' => '',
                'description' => '',
                'sort_order' => 1,
                'is_active' => '1',
                'variants' => [
                    [
                        'id' => $item->id,
                        'name' => 'Brownies Baru',
                        'description' => 'Deskripsi baru.',
                        'base_price' => 31000,
                        'image' => UploadedFile::fake()->image('brownies-baru.jpg'),
                        'sort_order' => 1,
                        'is_default' => '1',
                        'is_active' => '0',
                    ],
                ],
            ],
        );

        $response
            ->assertRedirect(route('menu.show', ['menu' => $item->id, 'type' => 'item']))
            ->assertSessionHas('success', 'Menu "Brownies Baru" berhasil diperbarui.');

        $this->assertDatabaseHas('menu_items', [
            'id' => $item->id,
            'menu_group_id' => null,
            'name' => 'Brownies Baru',
            'description' => 'Deskripsi baru.',
            'base_price' => '31000.00',
            'is_default' => true,
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('menu_images', [
            'menu_item_id' => $item->id,
            'is_primary' => true,
        ]);
    }

    public function test_single_menu_item_can_be_moved_into_another_group_from_edit_page(): void
    {
        $user = User::factory()->create();
        $targetGroup = MenuGroup::factory()->create([
            'name' => 'Paket Bento',
        ]);
        $item = MenuItem::factory()->create([
            'menu_group_id' => null,
            'name' => 'Dimsum Solo',
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)->put(
            route('menu.update', ['menu' => $item->id, 'type' => 'item']),
            [
                'variants' => [
                    [
                        'id' => $item->id,
                        'menu_group_id' => $targetGroup->id,
                        'name' => 'Dimsum Solo',
                        'base_price' => 27000,
                        'sort_order' => 1,
                        'is_active' => '1',
                    ],
                ],
            ],
        );

        $response
            ->assertRedirect(route('menu.show', $targetGroup))
            ->assertSessionHas('success', 'Menu "Dimsum Solo" berhasil dipindahkan ke grup.');

        $this->assertDatabaseHas('menu_items', [
            'id' => $item->id,
            'menu_group_id' => $targetGroup->id,
            'is_default' => true,
        ]);
    }

    public function test_group_variant_can_be_moved_out_of_group_from_group_edit_page(): void
    {
        $user = User::factory()->create();
        $category = MenuCategory::factory()->create([
            'name' => 'Snack',
        ]);
        $group = MenuGroup::factory()->create([
            'name' => 'Snack Box',
        ]);
        $remainingVariant = MenuItem::factory()->create([
            'menu_group_id' => $group->id,
            'name' => 'Snack Reguler',
            'sort_order' => 1,
            'is_default' => true,
        ]);
        $movedVariant = MenuItem::factory()->create([
            'menu_group_id' => $group->id,
            'menu_category_id' => $category->id,
            'name' => 'Snack Dipindah',
            'sort_order' => 2,
            'is_default' => false,
        ]);

        $response = $this->actingAs($user)->put(route('menu.update', $group), [
            'name' => $group->name,
            'variants' => [
                [
                    'id' => $remainingVariant->id,
                    'menu_group_id' => $group->id,
                    'name' => 'Snack Reguler',
                    'base_price' => 20000,
                    'sort_order' => 1,
                    'is_active' => '1',
                ],
                [
                    'id' => $movedVariant->id,
                    'menu_group_id' => null,
                    'menu_category_id' => null,
                    'name' => 'Snack Dipindah',
                    'base_price' => 22000,
                    'sort_order' => 2,
                    'is_active' => '1',
                ],
            ],
        ]);

        $response->assertRedirect(route('menu.show', $group));

        $this->assertDatabaseHas('menu_items', [
            'id' => $remainingVariant->id,
            'menu_group_id' => $group->id,
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('menu_items', [
            'id' => $movedVariant->id,
            'menu_group_id' => null,
            'menu_category_id' => null,
            'is_default' => true,
        ]);
    }
}
