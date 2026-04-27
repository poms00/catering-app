import type { MenuItem } from '@/types/menu/types';
import type { MenuFormData } from '../modal-form';
import type { VarianMenu } from './InformasiTable';

export type FormItemData = MenuFormData & {
    id?: number;
};

type MenuItemPreview = Pick<
    MenuItem,
    | 'menu_group_id'
    | 'menu_category_id'
    | 'description'
    | 'slug'
    | 'created_at'
    | 'updated_at'
    | 'created_by'
    | 'updated_by'
    | 'primary_image'
>;

export type DraftVarianMenu = VarianMenu & MenuItemPreview;

export function mapFormToMenuItem(data: FormItemData): DraftVarianMenu {
    return {
        id: data.id ?? Date.now(),
        menu_group_id: data.menu_group_id,
        menu_category_id: null,
        name: data.name,
        slug: data.name.toLowerCase().replace(/\s+/g, '-'),
        description: data.description,
        base_price: Number(data.base_price),
        is_default: data.is_default,
        is_active: data.is_active,
        sort_order: data.sort_order,
        image_url: data.imagePreview,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
        created_by: null,
        updated_by: null,
        primary_image: data.imagePreview
            ? {
                  id: Date.now(),
                  menu_item_id: null,
                  menu_group_id: null,
                  image_url: data.imagePreview,
                  is_primary: true,
                  sort_order: 1,
                  created_at: new Date().toISOString(),
              }
            : null,
    };
}

export function isFormItemValid(item: FormItemData): boolean {
    if (!item.name.trim()) {
        return false;
    }

    const price = Number(item.base_price);

    if (!item.base_price.trim() || Number.isNaN(price) || price <= 0) {
        return false;
    }

    if (item.requires_group) {
        if (!item.menu_group_id && !item.menu_group_name.trim()) {
            return false;
        }
    }

    return true;
}
