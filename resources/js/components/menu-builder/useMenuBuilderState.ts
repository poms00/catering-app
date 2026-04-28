import type { DragEndEvent } from '@dnd-kit/core';
import { arrayMove } from '@dnd-kit/sortable';
import { useCallback, useEffect, useRef, useState } from 'react';

import type {
    BuilderEntry,
    CategoryDraft,
    DragData,
    MenuBuilderEntryPayload,
    MenuBuilderPayload,
    MenuCategoryOption,
    VarianMenu,
} from '@/components/menu-builder/types';

type UseMenuBuilderStateOptions = {
    menuCategories: MenuCategoryOption[];
    initialEntries?: MenuBuilderEntryPayload[];
    saveRequestId?: number;
    allowRootItems?: boolean;
    confirmWrapperDelete?: boolean;
    onWrapperDelete?: (entry: MenuBuilderEntryPayload) => void;
    onSave: (payload: MenuBuilderPayload) => void;
};

function createVariant(): VarianMenu {
    return {
        id: Date.now() + Math.floor(Math.random() * 1000),
        menu_group_id: null,
        menu_category_id: null,
        menu_category_ids: [],
        name: '',
        description: '',
        image: null,
        imagePreview: null,
        base_price: '',
        is_active: true,
        sort_order: 1,
        is_default: true,
    };
}

function normalizeVariants(variants: VarianMenu[]): VarianMenu[] {
    return variants.map((variant, index) => ({
        ...variant,
        sort_order: index + 1,
        is_default: index === 0,
    }));
}

function normalizeEntries(entries: BuilderEntry[]): BuilderEntry[] {
    return entries.map((entry, index) => ({
        ...entry,
        sort_order: index + 1,
        variants: normalizeVariants(entry.variants),
    }));
}

function mapPayloadToEntry(
    entry: MenuBuilderEntryPayload,
    index: number,
): BuilderEntry {
    return {
        id: entry.id ?? Date.now() + index + Math.floor(Math.random() * 1000),
        type: entry.type,
        name: entry.name,
        description: entry.description,
        sort_order: entry.sort_order,
        is_active: entry.is_active,
        menu_category_id: entry.menu_category_id,
        variants: entry.variants,
    };
}

export function entryToPayload(
    entry: BuilderEntry,
    index: number,
): MenuBuilderEntryPayload {
    const defaultVariantCategoryId =
        entry.variants.find((variant) => variant.is_default)
            ?.menu_category_ids?.[0] ??
        entry.variants.find((variant) => variant.is_default)
            ?.menu_category_id ??
        entry.variants[0]?.menu_category_ids?.[0] ??
        entry.variants[0]?.menu_category_id ??
        entry.menu_category_id;
    const variants = normalizeVariants(entry.variants).map((variant) => ({
        ...variant,
        menu_category_ids:
            variant.menu_category_ids ??
            (variant.menu_category_id != null
                ? [variant.menu_category_id]
                : []),
        menu_group_id: entry.type === 'wrapper' ? entry.id : null,
    }));

    return {
        id: entry.id,
        type: entry.type,
        name: entry.name,
        description: entry.description,
        sort_order: index + 1,
        is_active: entry.is_active,
        menu_category_id: defaultVariantCategoryId,
        variants,
    };
}

function createWrapperEntry(): BuilderEntry {
    return {
        id: Date.now() + Math.floor(Math.random() * 1000),
        type: 'wrapper',
        name: '',
        description: '',
        sort_order: 1,
        is_active: true,
        menu_category_id: null,
        variants: [],
    };
}

function createSingleEntry(): BuilderEntry {
    return {
        id: Date.now() + Math.floor(Math.random() * 1000),
        type: 'single',
        name: '',
        description: '',
        sort_order: 1,
        is_active: true,
        menu_category_id: null,
        variants: [createVariant()],
    };
}

function createSingleEntryFromVariant(variant: VarianMenu): BuilderEntry {
    return {
        id: Date.now() + Math.floor(Math.random() * 1000),
        type: 'single',
        name: '',
        description: '',
        sort_order: 1,
        is_active: true,
        menu_category_id: null,
        variants: [
            {
                ...variant,
                menu_group_id: null,
                menu_category_ids:
                    variant.menu_category_ids ??
                    (variant.menu_category_id != null
                        ? [variant.menu_category_id]
                        : []),
            },
        ],
    };
}

function isDragData(value: unknown): value is DragData {
    return (
        typeof value === 'object' &&
        value !== null &&
        'type' in value &&
        typeof (value as { type: unknown }).type === 'string'
    );
}

function findEntryIndexByVariant(
    entries: BuilderEntry[],
    variantId: number,
): number {
    return entries.findIndex((entry) =>
        entry.variants.some((variant) => variant.id === variantId),
    );
}

function moveVariantBetweenEntries(
    entries: BuilderEntry[],
    activeData: Extract<DragData, { type: 'variant' }>,
    overData: DragData,
    allowRootItems: boolean,
): BuilderEntry[] {
    const sourceEntryIndex = findEntryIndexByVariant(
        entries,
        activeData.variantId,
    );

    if (sourceEntryIndex === -1) {
        return entries;
    }

    const sourceEntry = entries[sourceEntryIndex];
    const sourceVariantIndex = sourceEntry.variants.findIndex(
        (variant) => variant.id === activeData.variantId,
    );
    const movingVariant = sourceEntry.variants[sourceVariantIndex];

    if (!movingVariant) {
        return entries;
    }

    const overEntry =
        overData.type === 'container' && overData.entryId === null
            ? null
            : (entries.find((entry) => entry.id === overData.entryId) ?? null);

    const isRootTarget =
        overData.type === 'container' && overData.entryId === null
            ? true
            : overEntry?.type === 'single';

    if (isRootTarget) {
        if (!allowRootItems) {
            return entries;
        }

        const targetEntryIndex =
            overEntry?.type === 'single'
                ? entries.findIndex((entry) => entry.id === overEntry.id)
                : entries.length;

        if (sourceEntry.type === 'single' && overEntry?.type === 'single') {
            return arrayMove(entries, sourceEntryIndex, targetEntryIndex);
        }

        let nextEntries = entries
            .map((entry) =>
                entry.id === sourceEntry.id
                    ? {
                          ...entry,
                          variants: entry.variants.filter(
                              (variant) => variant.id !== movingVariant.id,
                          ),
                      }
                    : entry,
            )
            .filter(
                (entry) =>
                    entry.type === 'wrapper' || entry.variants.length > 0,
            );

        let insertIndex = targetEntryIndex;

        if (sourceEntry.type === 'single' && sourceEntryIndex < insertIndex) {
            insertIndex--;
        }

        insertIndex = Math.max(0, Math.min(insertIndex, nextEntries.length));
        nextEntries = [
            ...nextEntries.slice(0, insertIndex),
            createSingleEntryFromVariant(movingVariant),
            ...nextEntries.slice(insertIndex),
        ];

        return nextEntries;
    }

    if (!overEntry || overEntry.type !== 'wrapper') {
        return entries;
    }

    if (
        sourceEntry.id === overEntry.id &&
        overData.type === 'variant' &&
        overData.variantId !== movingVariant.id
    ) {
        const targetVariantIndex = overEntry.variants.findIndex(
            (variant) => variant.id === overData.variantId,
        );

        return entries.map((entry) =>
            entry.id === sourceEntry.id
                ? {
                      ...entry,
                      variants: arrayMove(
                          entry.variants,
                          sourceVariantIndex,
                          targetVariantIndex,
                      ),
                  }
                : entry,
        );
    }

    if (
        sourceEntry.id === overEntry.id &&
        (overData.type !== 'variant' || overData.variantId === movingVariant.id)
    ) {
        return entries;
    }

    const nextEntries = entries
        .map((entry) =>
            entry.id === sourceEntry.id
                ? {
                      ...entry,
                      variants: entry.variants.filter(
                          (variant) => variant.id !== movingVariant.id,
                      ),
                  }
                : entry,
        )
        .filter(
            (entry) => entry.type === 'wrapper' || entry.variants.length > 0,
        );

    return nextEntries.map((entry) => {
        if (entry.id !== overEntry.id) {
            return entry;
        }

        const insertIndex =
            overData.type === 'variant'
                ? Math.max(
                      0,
                      entry.variants.findIndex(
                          (variant) => variant.id === overData.variantId,
                      ),
                  )
                : entry.variants.length;

        return {
            ...entry,
            variants: [
                ...entry.variants.slice(0, insertIndex),
                movingVariant,
                ...entry.variants.slice(insertIndex),
            ],
        };
    });
}

export default function useMenuBuilderState({
    menuCategories,
    initialEntries = [],
    saveRequestId = 0,
    allowRootItems = true,
    confirmWrapperDelete = false,
    onWrapperDelete,
    onSave,
}: UseMenuBuilderStateOptions) {
    const hasMountedRef = useRef(false);
    const lastHandledSaveRequestIdRef = useRef(0);
    const nextCategoryTempIdRef = useRef(-1);
    const onSaveRef = useRef(onSave);
    const [entries, setEntries] = useState<BuilderEntry[]>(() =>
        normalizeEntries(initialEntries.map(mapPayloadToEntry)),
    );
    const [syncedInitialEntries, setSyncedInitialEntries] =
        useState(initialEntries);
    const [pendingDeleteEntry, setPendingDeleteEntry] =
        useState<BuilderEntry | null>(null);
    const [categoryDrafts, setCategoryDrafts] = useState<CategoryDraft[]>([]);

    useEffect(() => {
        onSaveRef.current = onSave;
    }, [onSave]);

    if (initialEntries !== syncedInitialEntries) {
        setSyncedInitialEntries(initialEntries);
        setEntries(normalizeEntries(initialEntries.map(mapPayloadToEntry)));
        setPendingDeleteEntry(null);
        setCategoryDrafts([]);
    }

    const updateEntries = useCallback(
        (
            payload:
                | BuilderEntry[]
                | ((current: BuilderEntry[]) => BuilderEntry[]),
        ) => {
            setEntries((current) =>
                normalizeEntries(
                    typeof payload === 'function' ? payload(current) : payload,
                ),
            );
        },
        [],
    );

    useEffect(() => {
        if (!hasMountedRef.current) {
            hasMountedRef.current = true;

            return;
        }

        if (
            saveRequestId === 0 ||
            saveRequestId === lastHandledSaveRequestIdRef.current
        ) {
            return;
        }

        lastHandledSaveRequestIdRef.current = saveRequestId;

        onSaveRef.current({
            entries: entries.map(entryToPayload),
            categoryDrafts,
        });
    }, [categoryDrafts, entries, saveRequestId]);

    const handleAddWrapper = useCallback(() => {
        updateEntries((current) => [createWrapperEntry(), ...current]);
    }, [updateEntries]);

    const handleAddSingle = useCallback(() => {
        updateEntries((current) => [createSingleEntry(), ...current]);
    }, [updateEntries]);

    const menuCategoryOptions = [
        ...menuCategories,
        ...categoryDrafts.map((draft) => ({
            id: draft.temp_id,
            name: draft.name,
        })),
    ];

    const handleCreateCategory = useCallback(
        (name: string): MenuCategoryOption => {
            const normalizedName = name.trim();
            const existingCategory = menuCategories.find(
                (category) =>
                    category.name.toLowerCase() ===
                    normalizedName.toLowerCase(),
            );
            const existingDraft = categoryDrafts.find(
                (draft) =>
                    draft.name.toLowerCase() === normalizedName.toLowerCase(),
            );

            if (existingCategory) {
                return existingCategory;
            }

            if (existingDraft) {
                return {
                    id: existingDraft.temp_id,
                    name: existingDraft.name,
                };
            }

            const draft = {
                temp_id: nextCategoryTempIdRef.current,
                name: normalizedName,
            };

            nextCategoryTempIdRef.current -= 1;
            setCategoryDrafts((current) => [...current, draft]);

            return {
                id: draft.temp_id,
                name: draft.name,
            };
        },
        [categoryDrafts, menuCategories],
    );

    const handleEntryDelete = useCallback(
        (entryId: number) => {
            const entry = entries.find((item) => item.id === entryId) ?? null;

            if (entry?.type === 'wrapper' && confirmWrapperDelete) {
                setPendingDeleteEntry(entry);

                return;
            }

            updateEntries((current) =>
                current.filter((item) => item.id !== entryId),
            );
        },
        [confirmWrapperDelete, entries, updateEntries],
    );

    const handleConfirmEntryDelete = useCallback(() => {
        if (!pendingDeleteEntry) {
            return;
        }

        if (pendingDeleteEntry.type === 'wrapper' && onWrapperDelete) {
            onWrapperDelete(entryToPayload(pendingDeleteEntry, 0));
            setPendingDeleteEntry(null);

            return;
        }

        updateEntries((current) =>
            current.filter((entry) => entry.id !== pendingDeleteEntry.id),
        );
        setPendingDeleteEntry(null);
    }, [onWrapperDelete, pendingDeleteEntry, updateEntries]);

    const handleEntryNameChange = useCallback(
        (entryId: number, value: string) => {
            updateEntries((current) =>
                current.map((entry) =>
                    entry.id === entryId ? { ...entry, name: value } : entry,
                ),
            );
        },
        [updateEntries],
    );

    const handleAddMenuToWrapper = useCallback(
        (entryId: number) => {
            updateEntries((current) =>
                current.map((entry) =>
                    entry.id === entryId
                        ? {
                              ...entry,
                              variants: [createVariant(), ...entry.variants],
                          }
                        : entry,
                ),
            );
        },
        [updateEntries],
    );

    const handleVariantEdit = useCallback(
        (entryId: number, variantId: number, payload: Partial<VarianMenu>) => {
            updateEntries((current) =>
                current.map((entry) =>
                    entry.id === entryId
                        ? {
                              ...entry,
                              variants: entry.variants.map((variant) =>
                                  variant.id === variantId
                                      ? { ...variant, ...payload }
                                      : variant,
                              ),
                          }
                        : entry,
                ),
            );
        },
        [updateEntries],
    );

    const handleVariantDelete = useCallback(
        (entryId: number, variantId: number) => {
            updateEntries((current) =>
                current.flatMap((entry) => {
                    if (entry.id !== entryId) {
                        return [entry];
                    }

                    if (entry.type === 'single') {
                        return [];
                    }

                    return [
                        {
                            ...entry,
                            variants: entry.variants.filter(
                                (variant) => variant.id !== variantId,
                            ),
                        },
                    ];
                }),
            );
        },
        [updateEntries],
    );

    const handleVariantReorder = useCallback(
        (entryId: number, variants: VarianMenu[]) => {
            updateEntries((current) =>
                current.map((entry) =>
                    entry.id === entryId ? { ...entry, variants } : entry,
                ),
            );
        },
        [updateEntries],
    );

    const handleBuilderDragEnd = useCallback(
        ({ active, over }: DragEndEvent) => {
            if (!over || active.id === over.id) {
                return;
            }

            const activeData = active.data.current;
            const overData = over.data.current;

            if (!isDragData(activeData) || !isDragData(overData)) {
                return;
            }

            if (activeData.type === 'variant') {
                updateEntries((current) =>
                    moveVariantBetweenEntries(
                        current,
                        activeData,
                        overData,
                        allowRootItems,
                    ),
                );

                return;
            }

            if (activeData.type !== 'entry' || overData.type !== 'entry') {
                return;
            }

            updateEntries((current) => {
                const oldIndex = current.findIndex(
                    (entry) => entry.id === activeData.entryId,
                );
                const newIndex = current.findIndex(
                    (entry) => entry.id === overData.entryId,
                );

                return arrayMove(current, oldIndex, newIndex);
            });
        },
        [allowRootItems, updateEntries],
    );

    return {
        entries,
        pendingDeleteEntry,
        menuCategoryOptions,
        handleAddWrapper,
        handleAddSingle,
        handleCreateCategory,
        handleEntryDelete,
        handleConfirmEntryDelete,
        handleEntryNameChange,
        handleAddMenuToWrapper,
        handleVariantEdit,
        handleVariantDelete,
        handleVariantReorder,
        handleBuilderDragEnd,
        setPendingDeleteEntry,
    };
}
