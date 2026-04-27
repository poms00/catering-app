<?php

namespace Tests\Feature\Menu;

use App\Models\MenuCategory;
use App\Models\MenuGroup;
use App\Models\MenuImage;
use App\Actions\Menu\MenuImageAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuImagePrimaryPromotionTest extends TestCase
{
    use RefreshDatabase;

    public function test_primary_image_is_promoted_to_another_image_after_deletion(): void
    {
        // Create necessary models
        $category = MenuCategory::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
        $group = MenuGroup::factory()->create([
            'menu_category_id' => $category->id,
            'slug' => 'test-group',
        ]);

        // Create two images for the group: one primary, one secondary
        $firstImage = MenuImage::factory()->create([
            'menu_group_id' => $group->id,
            'menu_item_id'  => null,
            'is_primary'    => true,
            'sort_order'    => 1,
            'image_url'     => 'https://example.com/img1.jpg',
        ]);
        $secondImage = MenuImage::factory()->create([
            'menu_group_id' => $group->id,
            'menu_item_id'  => null,
            'is_primary'    => false,
            'sort_order'    => 2,
            'image_url'     => 'https://example.com/img2.jpg',
        ]);

        $imageAction = new MenuImageAction();

        // Delete the primary image
        $imageAction->delete($firstImage);

        // Refresh the second image from the database
        $secondImage->refresh();

        // The second image should now be the primary
        $this->assertTrue($secondImage->is_primary);
        // The first image should be removed
        $this->assertDatabaseMissing('menu_images', ['id' => $firstImage->id]);
    }
}
