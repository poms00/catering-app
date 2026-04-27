/* eslint-disable react-hooks/set-state-in-effect */
import { useEffect, useState } from 'react';
import MenuCombobox from '@/components/menu/menu-form/fields/MenuCombobox';
import MenuMultiCombobox from '@/components/menu/menu-form/fields/MenuMultiCombobox';
import { Button } from '@/components/ui/button';
import { CardFooter } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';

import type { MenuGroup, MenuItem, MenuItemForm } from '@/types/menu/types';
import ImageUpload from './fields/ImageUpload';

export type MenuGroupOption = Pick<MenuGroup, 'id' | 'name'>;

export type MenuFormData = MenuItemForm & {
    requires_group: boolean;
    menu_group_name: string;
    menu_category_ids: number[];
    menu_category_name: string;
    imagePreview: string | null;
};

type MenuFormProps = {
    initialData?: Partial<MenuFormData>;
    menuCategories: { id: number; name: string }[];
    menuGroups: { id: number; name: string; menu_category_id: number }[];
    onSubmit: (data: MenuFormData) => void;
    onCancel?: () => void;
    isProcessing?: boolean;
    submitLabel?: string;
    errors?: Partial<Record<keyof MenuFormData, string>>;
};

export function createMenuForm(): MenuFormData {
    return {
        menu_group_id: null,
        name: '',
        base_price: '',
        description: '',
        is_default: false,
        sort_order: 1,
        is_active: true,
        image: null,
        requires_group: false,
        menu_group_name: '',
        menu_category_ids: [],
        menu_category_name: '',
        imagePreview: null,
    };
}

export function mapMenuToForm(menu: MenuItem): MenuFormData {
    return {
        menu_group_id: menu.menu_group_id,
        name: menu.name,
        base_price: String(menu.base_price ?? ''),
        description: menu.description ?? '',
        is_default: menu.is_default,
        sort_order: menu.sort_order,
        is_active: menu.is_active,
        image: null,
        requires_group: menu.menu_group_id !== null,
        menu_group_name: '',
        menu_category_ids: menu.menu_category_id ? [menu.menu_category_id] : [],
        menu_category_name: '',
        imagePreview:
            typeof menu.primary_image === 'string'
                ? menu.primary_image
                : (menu.primary_image?.image_url ?? null),
    };
}

export default function FormMenu({
    initialData,
    menuCategories,
    menuGroups,
    onSubmit,
    onCancel,
    errors = {},
    isProcessing = false,
    submitLabel = 'Simpan',
}: MenuFormProps) {
    const [form, setForm] = useState<MenuFormData>({
        ...createMenuForm(),
        ...initialData,
    });

    useEffect(() => {
        setForm({
            ...createMenuForm(),
            ...initialData,
        });
    }, [initialData]);

  

    function set<K extends keyof MenuFormData>(key: K, value: MenuFormData[K]) {
        setForm((prev) => ({ ...prev, [key]: value }));
    }

    function handleImageChange(file: File) {
        const reader = new FileReader();

        reader.onload = (event) => {
            setForm((prev) => ({
                ...prev,
                image: file,
                imagePreview: event.target?.result as string,
            }));
        };

        reader.readAsDataURL(file);
    }

    function handleImageRemove() {
        setForm((prev) => ({
            ...prev,
            image: null,
            imagePreview: null,
        }));
    }

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        onSubmit(form);
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            {/* IMAGE */}
            <div className="space-y-2">
                <Label className="text-sm font-medium">Foto Menu</Label>
                <ImageUpload
                    preview={form.imagePreview}
                    onChange={handleImageChange}
                    onRemove={handleImageRemove}
                    disabled={isProcessing}
                />
            </div>

            {/* CATEGORY */}
            <div className="space-y-2">
                <Label className="text-sm font-medium">Kategori Menu</Label>
                {/* CATEGORY */}
                {/* CATEGORY */}
                <MenuMultiCombobox
                    key={menuCategories.length}
                    items={menuCategories}
                    value={form.menu_category_ids}
                    placeholder="Pilih kategori"
                    emptyText="Tidak ada kategori ditemukan."
                    onValueChange={(ids) => set('menu_category_ids', ids)}
                />
            </div>

            {/* GROUP */}
            <div className="space-y-2">
                <Label className="text-sm font-medium">Grup Menu</Label>
                <MenuCombobox
                    key={form.menu_category_ids.join(',')}
                    items={menuGroups}
                    value={form.menu_group_id}
                    onValueChange={(option) =>
                        set('menu_group_id', option?.id ?? null)
                    }
                />
            </div>

            {/* NAME */}
            <div className="space-y-2">
                <Label htmlFor="name">Nama Menu</Label>
                <Input
                    id="name"
                    value={form.name}
                    onChange={(e) => set('name', e.target.value)}
                />
                {errors.name && (
                    <p className="text-xs text-destructive">{errors.name}</p>
                )}
            </div>

            {/* PRICE */}
            <div className="space-y-2">
                <Label htmlFor="base_price">Harga</Label>
                <Input
                    id="base_price"
                    type="number"
                    min={0}
                    value={form.base_price}
                    onChange={(e) => set('base_price', e.target.value)}
                />
                {errors.base_price && (
                    <p className="text-xs text-destructive">
                        {errors.base_price}
                    </p>
                )}
            </div>

            {/* DESCRIPTION */}
            <div className="space-y-2">
                <Label htmlFor="description">Deskripsi Menu</Label>
                <Textarea
                    id="description"
                    value={form.description}
                    onChange={(e) => set('description', e.target.value)}
                    rows={3}
                />
                {errors.description && (
                    <p className="text-xs text-destructive">
                        {errors.description}
                    </p>
                )}
            </div>

            {/* STATUS */}
            <div className="flex items-center gap-3">
                <Switch
                    id="is_active"
                    checked={form.is_active}
                    onCheckedChange={(checked) => set('is_active', checked)}
                />
                <Label htmlFor="is_active">
                    {form.is_active ? 'Aktif' : 'Nonaktif'}
                </Label>
            </div>

            {/* ACTION */}
            <CardFooter className="flex gap-2 border-t px-0 pt-4">
                <Button type="submit" disabled={isProcessing}>
                    {isProcessing ? 'Menyimpan…' : submitLabel}
                </Button>

                {onCancel && (
                    <Button type="button" variant="outline" onClick={onCancel}>
                        Batal
                    </Button>
                )}
            </CardFooter>
        </form>
    );
}
