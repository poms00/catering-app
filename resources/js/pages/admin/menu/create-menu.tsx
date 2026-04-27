import { Head, router } from '@inertiajs/react';
import { useCallback, useState } from 'react';

import Heading from '@/components/heading';
import EditGrup from '@/components/menu/menu-form/EditGrup';
import type { VarianMenu } from '@/components/menu/menu-form/InformasiTable';
import menuRoutes from '@/routes/menu';

export default function CreateMenu({
    grup,
    varianList,
    menuCategories,
    menuGroups,
}: {
    grup: any;
    varianList: any[];
    menuCategories: { id: number; name: string }[];
    menuGroups: { id: number; name: string; menu_category_id: number }[];
}) {
    const [saveRequestId, setSaveRequestId] = useState(0);

    const handleSimpan = useCallback(
        ({
            informasiGrup,
            varianList,
        }: {
            informasiGrup: any;
            varianList: VarianMenu[];
        }) => {
            const payload = {
                creates_with_group: informasiGrup.creates_with_group,
                menu_group_id: informasiGrup.menu_group_id,
                name: informasiGrup.name,
                description: informasiGrup.description,
                sort_order: informasiGrup.sort_order,
                is_active: informasiGrup.is_active,
                image: informasiGrup.image,
                variants: varianList,
            };

            if (grup?.id) {
                router.post(
                    menuRoutes.update.url(grup.id),
                    {
                        ...payload,
                        _method: 'put',
                    } as any,
                    { preserveScroll: true },
                );

                return;
            }

            router.post(menuRoutes.store.url(), payload as any);
        },
        [grup],
    );

    const handleBatal = () => {
        router.visit(menuRoutes.index().url);
    };

    return (
        <>
            <Head title="Tambah Menu" />

            <div className="flex h-full flex-1 flex-col overflow-x-auto rounded-xl px-8 py-4">
                <Heading
                    title="Tambah Menu"
                    description="Buat item menu baru dengan struktur data yang konsisten."
                    showBack
                    onBack={() => window.history.back()}
                    actions={[
                        {
                            title: 'Batal',
                            onClick: handleBatal,
                            variant: 'outline',
                        },
                        {
                            title: 'Simpan Opsi',
                            onClick: () =>
                                setSaveRequestId((current) => current + 1),
                        },
                    ]}
                />

                <EditGrup
                    grup={grup}
                    varianList={varianList ?? []}
                    menuCategories={menuCategories}
                    menuGroups={menuGroups}
                    saveRequestId={saveRequestId}
                    onCancel={handleBatal}
                    onSave={handleSimpan}
                />
            </div>
        </>
    );
}

CreateMenu.layout = {
    breadcrumbs: [
        { title: 'Menu', href: menuRoutes.index() },
        { title: 'Tambah', href: null, current: true },
    ],
};
