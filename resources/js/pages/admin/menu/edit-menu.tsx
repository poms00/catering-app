import { Head, router } from '@inertiajs/react';

import FormMenu from '@/components/menu/menu-form/Form-menu';
import { useMenuForm } from '@/hooks/menu/useMenuForm';

import menuRoutes from '@/routes/menu';

import type { MenuGroup, MenuItem } from '@/types/menu/types';

type EditMenuProps = {
    menu: MenuItem;
    menuGroups: MenuGroup[];
};

export default function EditMenu({ menu, menuGroups }: EditMenuProps) {
    const { form } = useMenuForm(menu);

    return (
        <>
            <Head title="Edit Menu" />

            <FormMenu
                menuCategories={[]} // Add appropriate categories if available
                menuGroups={menuGroups}
                initialData={form}
                submitLabel="Simpan perubahan"
                onSubmit={(data) => {
                    router.put(menuRoutes.update.url(menu.id), data);
                }}
                onCancel={() => window.history.back()}
            />
        </>
    );
}

EditMenu.layout = {
    breadcrumbs: [
        {
            title: 'Menu',
            href: menuRoutes.index(),
        },
        {
            title: 'Detail',
            href: null,
            current: true,
        },
        {
            title: 'Edit',
            href: null,
            current: true,
        },
    ],
};
