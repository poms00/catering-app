import { useEffect, useRef, useState } from 'react';

import { Separator } from '@/components/ui/separator';
import type { MenuGroup } from '@/types/menu/types';

import type { MenuFormData } from './Form-menu';
import FormMenu from './Form-menu';

import type { VarianMenu } from './InformasiTable';
import InformasiTable from './InformasiTable';

export type InformasiGrupForm = {
    creates_with_group: boolean;
    name: string;
    description: string;
    sort_order: number;
    is_active: boolean;
    image: File | null;
    menu_category_ids: number[];
};

export function createInformasiGrupForm(
    grup?: MenuGroup | null,
): InformasiGrupForm {
    return {
        creates_with_group: !!grup,
        name: grup?.name ?? '',
        description: grup?.description ?? '',
        sort_order: grup?.sort_order ?? 1,
        is_active: grup?.is_active ?? true,
        image: null,
        menu_category_ids: grup?.menu_category_id
            ? [grup.menu_category_id]
            : [],
    };
}

type EditGrupProps = {
    grup?: MenuGroup | null;
    varianList?: VarianMenu[];
    menuCategories: { id: number; name: string }[];
    menuGroups: { id: number; name: string; menu_category_id: number }[];
    saveRequestId?: number;
    onSave: (payload: {
        informasiGrup: InformasiGrupForm;
        varianList: VarianMenu[];
    }) => void;
    onCancel: () => void;
};

export default function EditGrup({
    grup = null,
    varianList = [],
    menuCategories,
    menuGroups,
    saveRequestId = 0,
    onSave,
}: EditGrupProps) {
    const [informasiGrup] = useState<InformasiGrupForm>(
        createInformasiGrupForm(grup),
    );

    const [varianListState, setVarianListState] =
        useState<VarianMenu[]>(varianList);

    const [formKey, setFormKey] = useState(0);

    const hasMountedRef = useRef(false);
    const informasiGrupRef = useRef(informasiGrup);
    const varianListRef = useRef(varianListState);

    const handleEditVarian = (
        varianId: number,
        payload: Partial<VarianMenu> | null,
    ) => {
        if (!payload) {
            return;
        }

        setVarianListState((current) =>
            current.map((varian) =>
                varian.id === varianId ? { ...varian, ...payload } : varian,
            ),
        );
    };

    const handleDeleteVarian = (varianId: number) => {
        if (!window.confirm('Hapus varian ini?')) {
            return;
        }

        setVarianListState((current) =>
            current.filter((varian) => varian.id !== varianId),
        );
    };

    const handleReorderVarian = (reordered: VarianMenu[]) => {
        setVarianListState(reordered);
    };

    useEffect(() => {
        informasiGrupRef.current = informasiGrup;
    }, [informasiGrup]);

    useEffect(() => {
        varianListRef.current = varianListState;
    }, [varianListState]);

    useEffect(() => {
        if (!hasMountedRef.current) {
            hasMountedRef.current = true;

            return;
        }

        onSave({
            informasiGrup: informasiGrupRef.current,
            varianList: varianListRef.current,
        });
    }, [saveRequestId, onSave]);

    return (
        <div className="flex flex-1 gap-0">
            <main className="flex-1 space-y-6 pr-6">
                <h2 className="text-sm font-semibold tracking-wider text-muted-foreground uppercase">
                    Informasi Varian
                </h2>

                <InformasiTable
                    varianList={varianListState}
                    onTambah={() => {}}
                    onEdit={handleEditVarian}
                    onReorder={handleReorderVarian}
                    onDelete={handleDeleteVarian}
                />

                <Separator />
            </main>

            <aside className="w-100 shrink-0 space-y-4 border-l pl-6">
                <h2 className="text-sm font-semibold tracking-wider text-muted-foreground uppercase">
                    Form Menu
                </h2>

                <FormMenu
                    key={formKey}
                    initialData={informasiGrup}
                    menuCategories={menuCategories}
                    menuGroups={menuGroups}
                    onSubmit={(data: MenuFormData) => {
                        const newItem: VarianMenu = {
                            id: Date.now(),
                            name: data.name,
                            base_price: data.base_price,
                            is_active: data.is_active,
                            imagePreview: data.imagePreview,
                            is_default: data.is_default,
                            sort_order: varianListState.length,
                        };

                        setVarianListState((prev) => [...prev, newItem]);
                        setFormKey((k) => k + 1);
                    }}
                    isProcessing={false}
                    submitLabel="Tambah"
                />
            </aside>
        </div>
    );
}
