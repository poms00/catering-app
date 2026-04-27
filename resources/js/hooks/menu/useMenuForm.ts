import { useCallback, useEffect, useRef, useState } from 'react';

import type { MenuFormData } from '@/components/menu/modal-form';
import { createMenuForm, mapMenuToForm } from '@/components/menu/modal-form';
import type { MenuItem } from '@/types/menu';

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

    return {
        form,
        set,
        reset,
        replaceForm,
    } as const;
}
