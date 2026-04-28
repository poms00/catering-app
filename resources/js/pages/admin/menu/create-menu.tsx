import { Head, router } from '@inertiajs/react';
import { useCallback, useState } from 'react';

import Heading from '@/components/heading';
import CreateMenuBuilder from '@/components/menu/menu-form/CreateMenuBuilder';
import type {
    CategoryDraft,
    CreateMenuBuilderEntryPayload,
} from '@/components/menu/menu-form/CreateMenuBuilder';
import menuRoutes from '@/routes/menu';

type CreateMenuPayload = {
    entries: CreateMenuBuilderEntryPayload[];
    category_drafts: CategoryDraft[];
};

function buildMenuPayload(
    entries: CreateMenuBuilderEntryPayload[],
    categoryDrafts: CategoryDraft[],
): CreateMenuPayload {
    return {
        entries,
        category_drafts: categoryDrafts,
    };
}

export default function CreateMenu({
    menuCategories,
    menuGroups,
}: {
    menuCategories: { id: number; name: string }[];
    menuGroups: { id: number; name: string; menu_category_id: number | null }[];
}) {
    const [saveRequestId, setSaveRequestId] = useState(0);

    const handleSimpan = useCallback(
        ({
            entries,
            categoryDrafts,
        }: {
            entries: CreateMenuBuilderEntryPayload[];
            categoryDrafts: CategoryDraft[];
        }) => {
            const payload = buildMenuPayload(entries, categoryDrafts);

            router.post(menuRoutes.store.url(), payload as any, {
                forceFormData: true,
            });
        },
        [],
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

                <CreateMenuBuilder
                    menuCategories={menuCategories}
                    menuGroups={menuGroups}
                    saveRequestId={saveRequestId}
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
