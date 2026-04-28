import type { UniqueIdentifier } from '@dnd-kit/core';

export type MenuCategoryOption = { id: number; name: string };

export type CategoryDraft = { temp_id: number; name: string };

export type MenuGroupOption = {
    id: number;
    name: string;
    menu_category_id: number | null;
};

export interface VarianMenu {
    id: number;
    menu_group_id?: number | null;
    menu_category_id?: number | null;
    menu_category_ids?: number[];
    name: string;
    description?: string | null;
    image?: File | null;
    image_url?: string | null;
    imagePreview?: string | null;
    base_price: number | string;
    is_active: boolean;
    sort_order?: number | null;
    is_default?: boolean;
}

export type BuilderEntryType = 'wrapper' | 'single';

export type BuilderEntry<TItem = VarianMenu> = {
    id: number;
    type: BuilderEntryType;
    name: string;
    description: string;
    sort_order: number;
    is_active: boolean;
    menu_category_id: number | null;
    variants: TItem[];
};

export type MenuBuilderEntryPayload<TItem = VarianMenu> = {
    id?: number;
    type: BuilderEntryType;
    name: string;
    description: string;
    sort_order: number;
    is_active: boolean;
    menu_category_id: number | null;
    variants: TItem[];
};

export type MenuBuilderPayload<TItem = VarianMenu> = {
    entries: MenuBuilderEntryPayload<TItem>[];
    categoryDrafts: CategoryDraft[];
};

export type DragData =
    | {
          type: 'entry';
          entryId: number;
      }
    | {
          type: 'variant';
          entryId: number;
          variantId: number;
      }
    | {
          type: 'container';
          entryId: number | null;
      };

export const ROOT_CONTAINER_ID = 'container:root';

export const entrySortableId = (entryId: number): UniqueIdentifier =>
    `entry:${entryId}`;

export const variantSortableId = (variantId: number): UniqueIdentifier =>
    `variant:${variantId}`;
