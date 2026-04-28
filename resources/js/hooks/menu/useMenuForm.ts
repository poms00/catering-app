import { useCallback, useEffect, useRef, useState } from 'react';

import type { MenuItem } from '@/types/menu/types';

type MenuFormData = {
    id: number | null;
    menu_group_id: number | null;
    menu_category_id: number | null;
    name: string;
    description: string;
    base_price: number | string;
    is_default: boolean;
    sort_order: number;
    is_active: boolean;
    image: File | null;
};

function createMenuForm(): MenuFormData {
    return {
        id: null,
        menu_group_id: null,
        menu_category_id: null,
        name: '',
        description: '',
        base_price: '',
        is_default: true,
        sort_order: 1,
        is_active: true,
        image: null,
    };
}

function mapMenuToForm(menu: MenuItem): MenuFormData {
    return {
        id: menu.id,
        menu_group_id: menu.menu_group_id,
        menu_category_id: menu.menu_category_id,
        name: menu.name,
        description: menu.description ?? '',
        base_price: menu.base_price,
        is_default: menu.is_default,
        sort_order: menu.sort_order,
        is_active: menu.is_active,
        image: null,
    };
}

export function useMenuForm(initialMenu?: MenuItem | null) {
    const [form, setForm] = useState<MenuFormData>(() =>
        initialMenu ? mapMenuToForm(initialMenu) : createMenuForm(),
    );

    // Simpan ID sebelumnya untuk deteksi perubahan menu yang nyata
    const prevIdRef = useRef<number | string | null | undefined>(
        initialMenu?.id,
    );

    useEffect(() => {
        const prevId = prevIdRef.current;
        const nextId = initialMenu?.id;

        // Tidak ada perubahan ID — skip
        if (prevId === nextId) {
            return;
        }

        prevIdRef.current = nextId;

        setForm(
            nextId != null ? mapMenuToForm(initialMenu!) : createMenuForm(),
        );
    }, [initialMenu?.id]); // eslint-disable-line react-hooks/exhaustive-deps
    // ↑ intentional: kita hanya peduli ID, bukan seluruh object

    const set = useCallback(
        <K extends keyof MenuFormData>(key: K, value: MenuFormData[K]) => {
            setForm((prev) => ({ ...prev, [key]: value }));
        },
        [],
    );

    const reset = useCallback(() => {
        setForm(createMenuForm());
    }, []);

    // Dipakai saat perlu replace seluruh form sekaligus (misal: load draft)
    const replaceForm = useCallback((data: MenuFormData) => {
        setForm(data);
    }, []);

    const updateForm = useCallback(
        (
            payload: MenuFormData | ((previous: MenuFormData) => MenuFormData),
        ) => {
            setForm((previous) =>
                typeof payload === 'function' ? payload(previous) : payload,
            );
        },
        [],
    );

    return {
        form,
        set,
        reset,
        replaceForm,
        updateForm,
    } as const;
}
