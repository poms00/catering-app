<?php

namespace App\Actions\Menu;

use App\Models\MenuImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MenuImageAction
{
    public function upload(
        UploadedFile $file,
        ?int $menuGroupId = null,
        ?int $menuItemId = null,
        bool $isPrimary = false,
    ): MenuImage {
        $folder = $menuItemId
            ? "menu/items/{$menuItemId}"
            : "menu/groups/{$menuGroupId}";

        $path = $file->store($folder, 'public');

        // Ambil max sort_order + cek existing dalam 1 query
        $aggregate = MenuImage::query()
            ->when($menuItemId,  fn ($q) => $q->where('menu_item_id',  $menuItemId))
            ->when($menuGroupId, fn ($q) => $q->where('menu_group_id', $menuGroupId))
            ->selectRaw('COUNT(*) as total, MAX(sort_order) as max_sort')
            ->first();

        $hasExisting   = $aggregate->total > 0;
        $nextSortOrder = ((int) $aggregate->max_sort) + 1;
        $shouldPrimary = $isPrimary || ! $hasExisting;

        if ($shouldPrimary && $hasExisting) {
            MenuImage::query()
                ->when($menuItemId,  fn ($q) => $q->where('menu_item_id',  $menuItemId))
                ->when($menuGroupId, fn ($q) => $q->where('menu_group_id', $menuGroupId))
                ->update(['is_primary' => false]);
        }

        return MenuImage::create([
            'menu_item_id'  => $menuItemId,
            'menu_group_id' => $menuGroupId,
            'image_url'     => Storage::url($path),
            'is_primary'    => $shouldPrimary,
            'sort_order'    => $nextSortOrder,
        ]);
    }

    public function setPrimary(MenuImage $image): void
    {
        // Reset primary lama lalu set yang baru — 2 query dalam 1 transaksi implisit
        MenuImage::query()
            ->when($image->menu_item_id,  fn ($q) => $q->where('menu_item_id',  $image->menu_item_id))
            ->when($image->menu_group_id, fn ($q) => $q->where('menu_group_id', $image->menu_group_id))
            ->update(['is_primary' => false]);

        $image->update(['is_primary' => true]);
    }

    public function delete(MenuImage $image): void
    {
        $wasPrimary  = $image->is_primary;
        $itemId      = $image->menu_item_id;
        $groupId     = $image->menu_group_id;

        // Hapus file dari storage
        Storage::disk('public')->delete(
            str_replace('/storage/', '', parse_url($image->image_url, PHP_URL_PATH))
        );

        $image->delete();

        // Promote gambar berikutnya jadi primary jika perlu
        if ($wasPrimary) {
            $nextImageId = MenuImage::query()
                ->when($itemId, fn ($q) => $q->where('menu_item_id', $itemId))
                ->when($groupId, fn ($q) => $q->where('menu_group_id', $groupId))
                ->orderBy('sort_order')
                ->value('id');

            if ($nextImageId) {
                $nextImage = MenuImage::find($nextImageId);
                if ($nextImage) {
                    $nextImage->update(['is_primary' => true]);
                }
            }
        }
    }
}
