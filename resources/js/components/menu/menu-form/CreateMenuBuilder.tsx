import InformasiMenu from '@/components/menu/menu-form/InformasiMenu';
import MenuBuilder from '@/components/menu-builder/MenuBuilder';
import SortableEntryCard from '@/components/menu-builder/SortableEntryCard';
import type {
    CategoryDraft,
    DragData,
    MenuBuilderEntryPayload,
    MenuBuilderPayload,
    MenuCategoryOption,
    MenuGroupOption,
} from '@/components/menu-builder/types';
import { variantSortableId } from '@/components/menu-builder/types';
import useMenuBuilderState from '@/components/menu-builder/useMenuBuilderState';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

export type { CategoryDraft };
export type CreateMenuBuilderEntryPayload = MenuBuilderEntryPayload;
export type CreateMenuBuilderPayload = MenuBuilderPayload;

type CreateMenuBuilderProps = {
    menuCategories: MenuCategoryOption[];
    menuGroups: MenuGroupOption[];
    initialEntries?: CreateMenuBuilderEntryPayload[];
    saveRequestId?: number;
    allowAddWrapper?: boolean;
    allowAddSingle?: boolean;
    allowRootItems?: boolean;
    allowEntryDrag?: boolean;
    confirmWrapperDelete?: boolean;
    onWrapperDelete?: (entry: CreateMenuBuilderEntryPayload) => void;
    onSave: (payload: CreateMenuBuilderPayload) => void;
};

export default function CreateMenuBuilder({
    menuCategories,
    menuGroups,
    initialEntries = [],
    saveRequestId = 0,
    allowAddWrapper = true,
    allowAddSingle = true,
    allowRootItems = true,
    allowEntryDrag = true,
    confirmWrapperDelete = false,
    onWrapperDelete,
    onSave,
}: CreateMenuBuilderProps) {
    const {
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
    } = useMenuBuilderState({
        menuCategories,
        initialEntries,
        saveRequestId,
        allowRootItems,
        confirmWrapperDelete,
        onWrapperDelete,
        onSave,
    });

    const builderDescription =
        allowAddWrapper || allowAddSingle
            ? 'Susun wrapper dan menu single, lalu tarik menu untuk memindahkan posisinya.'
            : allowRootItems
              ? 'Perbarui menu single pada alur yang sama dengan halaman tambah.'
              : 'Perbarui wrapper dan menu di dalamnya tanpa membuat entri root baru.';

    return (
        <>
            <MenuBuilder
                entries={entries}
                allowAddWrapper={allowAddWrapper}
                allowAddSingle={allowAddSingle}
                allowRootItems={allowRootItems}
                description={builderDescription}
                onAddWrapper={handleAddWrapper}
                onAddSingle={handleAddSingle}
                onDragEnd={handleBuilderDragEnd}
            >
                {(entry) => {
                    const isWrapper = entry.type === 'wrapper';
                    const table = (
                        <InformasiMenu
                            varianList={entry.variants}
                            enableInternalDnd={false}
                            getSortableId={(variant) =>
                                variantSortableId(variant.id)
                            }
                            getSortableData={(variant) =>
                                ({
                                    type: 'variant',
                                    entryId: entry.id,
                                    variantId: variant.id,
                                }) satisfies DragData
                            }
                            menuCategories={menuCategoryOptions}
                            menuGroups={menuGroups}
                            groupContext={
                                isWrapper
                                    ? {
                                          id: entry.id,
                                          name: entry.name,
                                          description: '',
                                          menuCategoryIds:
                                              entry.menu_category_id != null
                                                  ? [entry.menu_category_id]
                                                  : [],
                                          isActive: true,
                                      }
                                    : null
                            }
                            groupFieldMode={isWrapper ? 'context' : 'none'}
                            showCategoryField
                            canAddMore={isWrapper}
                            showHeader={false}
                            showFooterCount={false}
                            showGroupContainer={false}
                            onTambah={() => handleAddMenuToWrapper(entry.id)}
                            onEdit={(variantId, payload) =>
                                handleVariantEdit(entry.id, variantId, payload)
                            }
                            onReorder={(variants) =>
                                handleVariantReorder(entry.id, variants)
                            }
                            onDelete={(variantId) =>
                                handleVariantDelete(entry.id, variantId)
                            }
                            onCreateCategory={handleCreateCategory}
                        />
                    );

                    return isWrapper ? (
                        <SortableEntryCard
                            key={entry.id}
                            entry={entry}
                            dragDisabled={!allowEntryDrag}
                            onNameChange={(value) =>
                                handleEntryNameChange(entry.id, value)
                            }
                            onDelete={() => handleEntryDelete(entry.id)}
                            onAddMenu={() => handleAddMenuToWrapper(entry.id)}
                        >
                            {table}
                        </SortableEntryCard>
                    ) : (
                        <div key={entry.id}>{table}</div>
                    );
                }}
            </MenuBuilder>

            <AlertDialog
                open={pendingDeleteEntry !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setPendingDeleteEntry(null);
                    }
                }}
            >
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            Apakah Anda yakin ingin menghapus grup?
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            Grup
                            {pendingDeleteEntry?.name
                                ? ` "${pendingDeleteEntry.name}"`
                                : ''}{' '}
                            akan dihapus beserta menu di dalamnya. Tindakan ini
                            tidak bisa dibatalkan.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Batal</AlertDialogCancel>
                        <AlertDialogAction
                            className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                            onClick={handleConfirmEntryDelete}
                        >
                            Hapus Grup
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </>
    );
}
