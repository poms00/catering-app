import { Head, router } from '@inertiajs/react';
import { useCallback, useMemo, useState } from 'react';

import Heading from '@/components/heading';
import CreateMenuBuilder from '@/components/menu/menu-form/CreateMenuBuilder';
import type {
    CategoryDraft,
    CreateMenuBuilderEntryPayload,
} from '@/components/menu/menu-form/CreateMenuBuilder';
import type { VarianMenu } from '@/components/menu/menu-form/InformasiMenu';
import menuRoutes from '@/routes/menu';
import type { MenuGroup } from '@/types/menu/types';

type EditMenuPayload = {
    name: string;
    description: string;
    sort_order: number;
    is_active: boolean;
    image: File | null;
    menu_category_id: number | null;
    category_drafts: CategoryDraft[];
    variants: VarianMenu[];
};

type EditMenuProps = {
    grup: MenuGroup | null;
    varianList: VarianMenu[];
    menuCategories: { id: number; name: string }[];
    menuGroups: { id: number; name: string; menu_category_id: number | null }[];
    menuType?: 'group' | 'item';
    menuId?: number;
};

function buildMenuPayload(
    entry: CreateMenuBuilderEntryPayload,
    categoryDrafts: CategoryDraft[],
): EditMenuPayload {
    return {
        name: entry.name,
        description: entry.description,
        sort_order: entry.sort_order,
        is_active: entry.is_active,
        image: null,
        menu_category_id: entry.menu_category_id,
        category_drafts: categoryDrafts,
        variants: entry.variants,
    };
}

export default function EditMenu({
    grup,
    varianList,
    menuCategories,
    menuGroups,
    menuType = 'group',
    menuId,
}: EditMenuProps) {
    const [saveRequestId, setSaveRequestId] = useState(0);
    const targetId = grup?.id ?? menuId ?? varianList[0]?.id;
    const isStandaloneItem = menuType === 'item';
    const initialEntries = useMemo<CreateMenuBuilderEntryPayload[]>(() => {
        if (isStandaloneItem) {
            const variant = varianList[0];

            return variant
                ? [
                      {
                          id: menuId ?? variant.id,
                          type: 'single',
                          name: '',
                          description: '',
                          sort_order: variant.sort_order ?? 1,
                          is_active: variant.is_active,
                          menu_category_id: variant.menu_category_id ?? null,
                          variants: [
                              {
                                  ...variant,
                                  menu_group_id: null,
                                  menu_category_ids:
                                      variant.menu_category_ids ??
                                      (variant.menu_category_id != null
                                          ? [variant.menu_category_id]
                                          : []),
                                  menu_category_id:
                                      variant.menu_category_id ?? null,
                              },
                          ],
                      },
                  ]
                : [];
        }

        if (!grup) {
            return [];
        }

        return [
            {
                id: grup.id,
                type: 'wrapper',
                name: grup.name,
                description: grup.description ?? '',
                sort_order: grup.sort_order ?? 1,
                is_active: grup.is_active,
                menu_category_id: grup.menu_category_id ?? null,
                variants: varianList.map((variant) => ({
                    ...variant,
                    menu_group_id: grup.id,
                    menu_category_ids:
                        variant.menu_category_ids ??
                        (variant.menu_category_id != null
                            ? [variant.menu_category_id]
                            : []),
                    menu_category_id: variant.menu_category_id ?? null,
                })),
            },
        ];
    }, [grup, isStandaloneItem, menuId, varianList]);

    const handleSimpan = useCallback(
        ({
            entries,
            categoryDrafts,
        }: {
            entries: CreateMenuBuilderEntryPayload[];
            categoryDrafts: CategoryDraft[];
        }) => {
            if (targetId == null) {
                return;
            }

            const entry = isStandaloneItem
                ? (entries.find((item) => item.type === 'single') ?? entries[0])
                : (entries.find((item) => item.type === 'wrapper') ??
                  entries[0]);

            if (!entry) {
                return;
            }

            router.post(
                menuRoutes.update.url(
                    targetId,
                    isStandaloneItem
                        ? {
                              query: { type: 'item' },
                          }
                        : undefined,
                ),
                {
                    ...buildMenuPayload(entry, categoryDrafts),
                    _method: 'put',
                } as never,
                {
                    preserveScroll: true,
                    forceFormData: true,
                },
            );
        },
        [isStandaloneItem, targetId],
    );

    const handleDeleteWrapper = useCallback(() => {
        if (!grup?.id || isStandaloneItem) {
            return;
        }

        router.delete(menuRoutes.destroy.url(grup.id));
    }, [grup, isStandaloneItem]);

    const handleBatal = useCallback(() => {
        if (targetId == null) {
            router.visit(menuRoutes.index().url);

            return;
        }

        router.visit(
            menuRoutes.show.url(
                targetId,
                isStandaloneItem ? { query: { type: 'item' } } : undefined,
            ),
        );
    }, [isStandaloneItem, targetId]);

    return (
        <>
            <Head title="Edit Menu" />

            <div className="flex h-full flex-1 flex-col overflow-x-auto rounded-xl px-8 py-4">
                <Heading
                    title="Edit Menu"
                    description={
                        isStandaloneItem
                            ? 'Perbarui menu single pada halaman edit yang sama.'
                            : 'Perbarui grup dan varian menu dengan alur yang sama seperti halaman tambah.'
                    }
                    showBack
                    onBack={() => window.history.back()}
                    actions={[
                        {
                            title: 'Batal',
                            onClick: handleBatal,
                            variant: 'outline',
                        },
                        {
                            title: 'Simpan Perubahan',
                            onClick: () =>
                                setSaveRequestId((current) => current + 1),
                        },
                    ]}
                />

                <CreateMenuBuilder
                    initialEntries={initialEntries}
                    menuCategories={menuCategories}
                    menuGroups={menuGroups}
                    allowAddWrapper={false}
                    allowAddSingle={false}
                    allowRootItems={isStandaloneItem}
                    allowEntryDrag={false}
                    confirmWrapperDelete={!isStandaloneItem}
                    saveRequestId={saveRequestId}
                    onWrapperDelete={handleDeleteWrapper}
                    onSave={handleSimpan}
                />
            </div>
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
            current: false,
        },
        {
            title: 'Edit',
            href: null,
            current: true,
        },
    ],
};
