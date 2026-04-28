// resources/js/types/menu/types.ts
// Generated from catering_bsj.sql schema

/* ─────────────────────────────────────────
   Shared / Primitives
───────────────────────────────────────── */

export type ISODateString = string; // "2024-01-15T10:30:00.000Z"
export type DateString = string; // "2024-01-15"

/* ─────────────────────────────────────────
   Menu Image
───────────────────────────────────────── */

export interface MenuImage {
    id: number;
    menu_item_id: number | null;
    menu_group_id: number | null;
    image_url: string;
    is_primary: boolean;
    sort_order: number;
    created_at: ISODateString;
}

export interface MenuCategorySummary {
    id: number;
    name: string;
}

/* ═══════════════════════════════════════════
   MENU GROUP
═══════════════════════════════════════════ */

/** Full model — dari database (list, detail, edit) */
export interface MenuGroup {
    id: number;
    menu_category_id: number | null;
    name: string;
    slug: string;
    description: string | null;
    sort_order: number;
    is_active: boolean;
    created_at: ISODateString;
    updated_at: ISODateString;
    created_by: number | null;
    updated_by: number | null;

    // Relations (eager loaded)
    images?: MenuImage[];
    primary_image?: MenuImage | null;
    items?: MenuItemSummary[];
    items_count?: number;
    menu_category?: MenuCategorySummary | null;
    menu_items?: MenuItem[];
}

/** Summary — dipakai dalam list tanpa relasi berat */
export interface MenuGroupSummary {
    id: number;
    menu_category_id: number | null;
    name: string;
    slug: string;
    description: string | null;
    sort_order: number;
    is_active: boolean;
    items_count: number;
    primary_image: MenuImage | null;
    created_at: ISODateString;
    updated_at: ISODateString;
}

/* ── MenuGroup Forms ── */

/** Form create grup baru */
export interface MenuGroupForm {
    menu_category_id: number | null;
    name: string;
    description: string;
    sort_order: number;
    is_active: boolean;
    /** File object — hanya ada di client saat upload */
    image?: File | null;
}

/** Form edit grup — sama dengan create, id disertakan */
export interface MenuGroupEditForm extends MenuGroupForm {
    id: number;
}

/** Payload partial untuk inline update (mis. toggle status, reorder) */
export type MenuGroupPatchPayload = Partial<
    Pick<
        MenuGroup,
        'name' | 'description' | 'sort_order' | 'is_active' | 'menu_category_id'
    >
>;

/* ── MenuGroup Page Props (Inertia) ── */

export interface MenuGroupListProps {
    groups: MenuGroupSummary[];
    filters?: {
        search?: string;
        is_active?: boolean | null;
    };
}

export interface MenuGroupDetailProps {
    group: MenuGroup;
    items: MenuItemSummary[];
}

export interface MenuGroupCreateProps {
    group?: null;
    categories: Array<{ id: number; name: string }>;
}

export interface MenuGroupEditProps {
    group: MenuGroup;
    items: MenuItemSummary[];
    categories: Array<{ id: number; name: string }>;
}

/* ═══════════════════════════════════════════
   MENU ITEM
═══════════════════════════════════════════ */

/** Full model — dari database */
export interface MenuItem {
    nama_menu: string;
    id: number;
    menu_group_id: number | null;
    menu_category_id: number | null;
    menu_category_ids?: number[];
    name: string;
    slug: string;
    base_price: number;
    description: string | null;
    is_default: boolean;
    sort_order: number;
    is_active: boolean;
    created_at: ISODateString;
    updated_at: ISODateString;
    created_by: number | null;
    updated_by: number | null;

    // Relations (eager loaded)
    images?: MenuImage[];
    primary_image?: MenuImage | string | null;
    group?: MenuGroupSummary | null;
    menu_group?: MenuGroupSummary | null;
    menu_category?: MenuCategorySummary | null;
    menu_categories?: MenuCategorySummary[];
}

/** Summary — dipakai dalam list / table varian */
export interface MenuItemSummary {
    id: number;
    menu_group_id: number | null;
    name: string;
    slug: string;
    base_price: number;
    description: string | null;
    is_default: boolean;
    sort_order: number;
    is_active: boolean;
    primary_image: MenuImage | null;
    created_at: ISODateString;
    updated_at: ISODateString;
}

/* ── MenuItem Forms ── */

/** Form create item baru */
export interface MenuItemForm {
    menu_group_id: number | null;
    name: string;
    base_price: number | string;
    description: string;
    is_default: boolean;
    sort_order: number;
    is_active: boolean;
    /** File object — hanya ada di client saat upload */
    image?: File | null;
}

/** Form edit item — id disertakan */
export interface MenuItemEditForm extends MenuItemForm {
    id: number;
}

/** Payload partial untuk inline update (mis. toggle status, rename, harga) */
export type MenuItemPatchPayload = Partial<
    Pick<
        MenuItem,
        | 'name'
        | 'base_price'
        | 'description'
        | 'is_default'
        | 'sort_order'
        | 'is_active'
        | 'menu_group_id'
    >
>;

/* ── MenuItem Page Props (Inertia) ── */

export interface MenuItemListProps {
    items: MenuItemSummary[];
    group?: MenuGroupSummary | null;
    filters?: {
        search?: string;
        is_active?: boolean | null;
        menu_group_id?: number | null;
    };
}

export interface MenuItemDetailProps {
    item: MenuItem;
}

export interface MenuItemCreateProps {
    item?: null;
    /** Daftar grup untuk dropdown */
    groups: Array<{ id: number; name: string }>;
    /** Jika membuat item langsung dari halaman grup */
    default_group_id?: number | null;
}

export interface MenuItemEditProps {
    item: MenuItem;
    groups: Array<{ id: number; name: string }>;
}

export type MenuImageItem = MenuImage;
export type MenuItemVariant = MenuItem;

/* ═══════════════════════════════════════════
   REORDER
═══════════════════════════════════════════ */

/** Payload POST reorder item dalam sebuah grup */
export interface ReorderPayload {
    /** Array of id sesuai urutan baru */
    urutan: number[];
}

/* ═══════════════════════════════════════════
   RE-EXPORT convenience
═══════════════════════════════════════════ */

export type { MenuGroup as Grup, MenuItem as Item, MenuImage as GambarMenu };
