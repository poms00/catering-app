import {
    DndContext,
    DragOverlay,
    KeyboardSensor,
    PointerSensor,
    closestCenter,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import type {
    DragEndEvent,
    DragStartEvent,
    UniqueIdentifier,
} from '@dnd-kit/core';
import {
    SortableContext,
    arrayMove,
    sortableKeyboardCoordinates,
    useSortable,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { GripVertical, LogOut, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

import ImageUpload from '@/components/menu/menu-form/fields/ImageUpload';
import MenuMultiCombobox from '@/components/menu/menu-form/fields/MenuMultiCombobox';
import type {
    MenuCategoryOption,
    MenuGroupOption,
    VarianMenu,
} from '@/components/menu-builder/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';

export type { VarianMenu };

type GroupContext = {
    id: number | null;
    name: string;
    description: string;
    menuCategoryIds: number[];
    isActive: boolean;
};

export type GroupFieldMode = 'context' | 'select' | 'none';

type SortableCardProps = {
    varian: VarianMenu;
    index: number;
    sortableId?: UniqueIdentifier;
    sortableData?: Record<string, unknown>;
    dragDisabled?: boolean;
    menuCategories: MenuCategoryOption[];
    selectedCategoryIds: number[];
    groupFieldMode?: GroupFieldMode;
    showCategoryField?: boolean;
    isSelected?: boolean;
    isDragOverlay?: boolean;
    onDetachFromGroup?: (varianId: number) => void;
    onCreateCategory?: (name: string) => MenuCategoryOption;
    onToggleStatus: (varianId: number, value: boolean) => void;
    onEdit: (varianId: number, payload: Partial<VarianMenu>) => void;
    onDelete: (varianId: number) => void;
};

export type InformasiMenuProps = {
    varianList?: VarianMenu[];
    selectedVarianId?: number | null;
    enableInternalDnd?: boolean;
    getSortableId?: (varian: VarianMenu) => UniqueIdentifier;
    getSortableData?: (
        varian: VarianMenu,
    ) => Record<string, unknown> | undefined;
    menuCategories: MenuCategoryOption[];
    menuGroups: MenuGroupOption[];
    groupContext?: GroupContext | null;
    groupFieldMode?: GroupFieldMode;
    showCategoryField?: boolean;
    canAddMore?: boolean;
    addButtonLabel?: string;
    showHeader?: boolean;
    showFooterCount?: boolean;
    showGroupContainer?: boolean;
    onDetachFromGroup?: (varianId: number) => void;
    onCreateCategory?: (name: string) => MenuCategoryOption;
    onTambah: () => void;
    onEdit: (varianId: number, payload: Partial<VarianMenu>) => void;
    onReorder: (items: VarianMenu[]) => void;
    onDelete: (varianId: number) => void;
};

const formatRupiah = (value: number | string) =>
    new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(value) || 0);

function SortableCard({
    varian,
    index,
    sortableId,
    sortableData,
    dragDisabled = false,
    menuCategories,
    selectedCategoryIds,
    groupFieldMode = 'select',
    showCategoryField = true,
    isSelected = false,
    isDragOverlay = false,
    onDetachFromGroup,
    onCreateCategory,
    onToggleStatus,
    onEdit,
    onDelete,
}: SortableCardProps) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({
        id: sortableId ?? varian.id,
        data: sortableData,
        disabled: dragDisabled || isDragOverlay,
    });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
    };

    const cardClassName = [
        'rounded-lg border bg-background/95 p-4 shadow-xs transition-[box-shadow,opacity,transform]',
        'hover:shadow-sm',
        isDragging && !isDragOverlay ? 'opacity-40' : '',
        isSelected && !isDragOverlay
            ? 'border-primary/40 ring-2 ring-primary/15'
            : '',
        isDragOverlay ? 'shadow-lg ring-2 ring-primary/20' : '',
    ]
        .filter(Boolean)
        .join(' ');

    const handleImageChange = (file: File) => {
        const reader = new FileReader();

        reader.onload = (event) => {
            onEdit(varian.id, {
                image: file,
                imagePreview: event.target?.result as string,
            });
        };

        reader.readAsDataURL(file);
    };

    const handleImageRemove = () => {
        onEdit(varian.id, {
            image: null,
            imagePreview: null,
        });
    };

    const categoryField = !showCategoryField ? null : (
        <div className="space-y-1.5">
            <Label className="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">
                Kategori
            </Label>
            <MenuMultiCombobox
                id={`cat-${varian.id}`}
                name={`cat_${varian.id}`}
                items={menuCategories}
                value={selectedCategoryIds}
                placeholder="Pilih atau buat tag kategori"
                className="w-full min-w-0"
                triggerClassName="w-full min-w-0"
                valueClassName="block max-w-full truncate text-left"
                allowCreate
                createLabel="Tambah kategori"
                onCreateOption={onCreateCategory}
                onValueChange={(ids) => {
                    onEdit(varian.id, {
                        menu_category_ids: ids,
                        menu_category_id: ids[0] ?? null,
                    });
                }}
            />
        </div>
    );

    return (
        <div ref={setNodeRef} style={style} className={cardClassName}>
            <div className="grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center">
                <div className="flex flex-wrap items-center gap-2">
                    <button
                        {...(dragDisabled || isDragOverlay ? {} : attributes)}
                        {...(dragDisabled || isDragOverlay ? {} : listeners)}
                        className="flex h-8 w-8 cursor-grab items-center justify-center rounded-md border border-transparent text-muted-foreground transition-colors hover:border-border hover:bg-muted hover:text-foreground active:cursor-grabbing disabled:cursor-default disabled:opacity-50"
                        aria-label="Drag to reorder"
                        type="button"
                        disabled={dragDisabled || isDragOverlay}
                    >
                        <GripVertical className="h-4 w-4" />
                    </button>

                    <Badge
                        variant="outline"
                        className="px-2 py-0.5 text-[11px]"
                    >
                        Sort #{index + 1}
                    </Badge>

                    <Badge
                        variant={varian.is_default ? 'default' : 'secondary'}
                        className="px-2 py-0.5 text-[11px]"
                    >
                        {varian.is_default ? 'Default' : 'Varian'}
                    </Badge>
                </div>

                <div className="inline-flex h-10 w-fit max-w-full items-center justify-self-start overflow-hidden rounded-md border bg-background shadow-xs sm:justify-self-end">
                    <div className="inline-flex h-full items-center gap-2 bg-muted/20 px-3">
                        <span className="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">
                            Aktif
                        </span>
                        <Switch
                            checked={varian.is_active}
                            onCheckedChange={(value) =>
                                onToggleStatus(varian.id, value)
                            }
                        />
                    </div>

                    {onDetachFromGroup && groupFieldMode === 'context' && (
                        <Button
                            size="icon"
                            variant="ghost"
                            className="h-10 w-10 rounded-none border-l text-muted-foreground hover:bg-muted hover:text-foreground"
                            onClick={() => onDetachFromGroup(varian.id)}
                            type="button"
                            title="Keluarkan dari grup"
                            aria-label="Keluarkan dari grup"
                        >
                            <LogOut className="h-4 w-4" />
                        </Button>
                    )}
                    <Button
                        size="icon"
                        variant="ghost"
                        className="h-10 w-10 rounded-none border-l text-destructive hover:bg-destructive/10 hover:text-destructive"
                        onClick={() => onDelete(varian.id)}
                        type="button"
                        title="Hapus menu"
                        aria-label="Hapus menu"
                    >
                        <Trash2 className="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <div className="mt-4 grid gap-4 lg:grid-cols-[116px_minmax(0,1fr)_minmax(0,1fr)] lg:items-start">
                <div className="flex flex-col items-center space-y-2">
                    <Label className="text-center text-[11px] font-medium tracking-wide text-muted-foreground uppercase">
                        Foto Menu
                    </Label>

                    <ImageUpload
                        id={`menu-image-${varian.id}`}
                        name={`image_${varian.id}`}
                        preview={
                            varian.imagePreview ?? varian.image_url ?? null
                        }
                        onChange={handleImageChange}
                        onRemove={handleImageRemove}
                        disabled={false}
                        containerClassName="flex justify-center"
                        uploadClassName="h-[116px] w-[116px] rounded-lg"
                    />
                </div>

                <div className="min-w-0 space-y-3">
                    {categoryField}
                    <div className="grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(9rem,12rem)]">
                        <div className="min-w-0 space-y-1.5">
                            <Label className="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">
                                Nama Menu
                            </Label>
                            <Input
                                value={varian.name}
                                className="h-9"
                                onChange={(event) =>
                                    onEdit(varian.id, {
                                        name: event.target.value,
                                    })
                                }
                            />
                        </div>

                        <div className="space-y-1.5">
                            <Label className="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">
                                Harga
                            </Label>
                            <Input
                                value={formatRupiah(varian.base_price)}
                                className="h-9"
                                onChange={(event) =>
                                    onEdit(varian.id, {
                                        base_price: event.target.value.replace(
                                            /[^0-9]/g,
                                            '',
                                        ),
                                    })
                                }
                            />
                        </div>
                    </div>
                </div>

                <div className="space-y-1.5">
                    <Label className="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">
                        Deskripsi
                    </Label>
                    <Textarea
                        rows={2}
                        value={varian.description ?? ''}
                        className="min-h-[116px] resize-none"
                        onChange={(event) =>
                            onEdit(varian.id, {
                                description: event.target.value,
                            })
                        }
                    />
                </div>
            </div>
        </div>
    );
}

export default function InformasiMenu({
    varianList = [],
    selectedVarianId = null,
    enableInternalDnd = true,
    getSortableId,
    getSortableData,
    menuCategories,
    groupContext = null,
    groupFieldMode = 'select',
    showCategoryField = true,
    canAddMore = true,
    addButtonLabel = 'Tambah Menu',
    showHeader = true,
    showFooterCount = true,
    showGroupContainer = true,
    onDetachFromGroup,
    onCreateCategory,
    onTambah,
    onEdit,
    onReorder,
    onDelete,
}: InformasiMenuProps) {
    const [activeId, setActiveId] = useState<number | null>(null);
    const [items, setItems] = useState<VarianMenu[]>(varianList);
    const [prevVarianList, setPrevVarianList] = useState(varianList);

    if (varianList !== prevVarianList) {
        setPrevVarianList(varianList);
        setItems(varianList);
    }

    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: { distance: 6 },
        }),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        }),
    );

    const activeVarian = activeId
        ? (items.find((variant) => variant.id === activeId) ?? null)
        : null;

    const handleDragStart = ({ active }: DragStartEvent) => {
        setActiveId(active.id as number);
    };

    const handleDragEnd = ({ active, over }: DragEndEvent) => {
        setActiveId(null);

        if (!over || active.id === over.id) {
            return;
        }

        const oldIndex = items.findIndex((variant) => variant.id === active.id);
        const newIndex = items.findIndex((variant) => variant.id === over.id);
        const reordered = arrayMove(items, oldIndex, newIndex).map(
            (variant, index) => ({
                ...variant,
                sort_order: index + 1,
            }),
        );

        setItems(reordered);
        onReorder(reordered);
    };

    const handleDragCancel = () => {
        setActiveId(null);
    };

    const handleToggleStatus = (varianId: number, value: boolean) => {
        onEdit(varianId, { is_active: value });
    };

    const resolveCategoryIds = (varian: VarianMenu): number[] => {
        if (Array.isArray(varian.menu_category_ids)) {
            return varian.menu_category_ids;
        }

        const categoryId = varian.menu_category_id ?? null;

        return categoryId != null ? [categoryId] : [];
    };

    const itemsContent = (
        <div className="space-y-4">
            {items.length === 0 ? (
                <div className="rounded-xl border border-dashed bg-muted/20 py-14 text-center text-sm text-muted-foreground">
                    Belum ada menu. Klik <strong>{addButtonLabel}</strong> untuk
                    memulai.
                </div>
            ) : (
                items.map((varian, index) => {
                    const selectedCategoryIds = resolveCategoryIds(varian);

                    return (
                        <SortableCard
                            key={varian.id}
                            varian={varian}
                            index={index}
                            sortableId={getSortableId?.(varian)}
                            sortableData={getSortableData?.(varian)}
                            dragDisabled={
                                !enableInternalDnd && getSortableId == null
                            }
                            menuCategories={menuCategories}
                            selectedCategoryIds={selectedCategoryIds}
                            groupFieldMode={groupFieldMode}
                            showCategoryField={showCategoryField}
                            isSelected={selectedVarianId === varian.id}
                            onDetachFromGroup={onDetachFromGroup}
                            onCreateCategory={onCreateCategory}
                            onToggleStatus={handleToggleStatus}
                            onEdit={onEdit}
                            onDelete={onDelete}
                        />
                    );
                })
            )}
        </div>
    );

    const sortableContent = (
        <SortableContext
            items={items.map((variant) =>
                getSortableId ? getSortableId(variant) : variant.id,
            )}
            strategy={verticalListSortingStrategy}
        >
            {groupContext && showGroupContainer ? (
                <section className="rounded-2xl border bg-muted/10 p-4">
                    <div className="flex flex-wrap items-center justify-between gap-3 border-b pb-4">
                        <h4 className="text-lg font-semibold">
                            {groupContext.name || 'Grup tanpa nama'}
                        </h4>

                        <Button
                            onClick={onTambah}
                            size="sm"
                            className="gap-1.5"
                            type="button"
                            disabled={!canAddMore}
                        >
                            <Plus className="h-4 w-4" />
                            {addButtonLabel}
                        </Button>
                    </div>

                    <div className="mt-4 space-y-4">{itemsContent}</div>
                </section>
            ) : groupContext ? (
                itemsContent
            ) : (
                itemsContent
            )}
        </SortableContext>
    );

    return (
        <div className="space-y-4">
            {showHeader && (
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 className="text-base font-semibold">Varian Menu</h3>
                        <p className="text-sm text-muted-foreground">
                            Tambah varian langsung ke daftar dan edit semuanya
                            di tempat yang sama.
                        </p>
                        {selectedVarianId !== null && (
                            <p className="mt-1 text-sm font-medium text-primary">
                                Varian baru siap diedit
                            </p>
                        )}
                    </div>

                    {!groupContext && (
                        <Button
                            onClick={onTambah}
                            size="sm"
                            className="gap-1.5"
                            type="button"
                            disabled={!canAddMore}
                        >
                            <Plus className="h-4 w-4" />
                            {addButtonLabel}
                        </Button>
                    )}
                </div>
            )}

            {!canAddMore && (
                <p className="text-sm text-muted-foreground">
                    Menu Tanpa Grup.
                </p>
            )}

            {enableInternalDnd ? (
                <DndContext
                    sensors={sensors}
                    collisionDetection={closestCenter}
                    onDragStart={handleDragStart}
                    onDragEnd={handleDragEnd}
                    onDragCancel={handleDragCancel}
                >
                    {sortableContent}

                    <DragOverlay
                        dropAnimation={{
                            duration: 180,
                            easing: 'cubic-bezier(0.18, 0.67, 0.6, 1.22)',
                        }}
                    >
                        {activeVarian ? (
                            <div className="w-[min(100vw-2rem,1100px)]">
                                <SortableCard
                                    varian={activeVarian}
                                    index={items.findIndex(
                                        (variant) =>
                                            variant.id === activeVarian.id,
                                    )}
                                    menuCategories={menuCategories}
                                    selectedCategoryIds={resolveCategoryIds(
                                        activeVarian,
                                    )}
                                    groupFieldMode={groupFieldMode}
                                    showCategoryField={showCategoryField}
                                    isDragOverlay
                                    onDetachFromGroup={undefined}
                                    onCreateCategory={onCreateCategory}
                                    onToggleStatus={() => {}}
                                    onEdit={() => {}}
                                    onDelete={() => {}}
                                />
                            </div>
                        ) : null}
                    </DragOverlay>
                </DndContext>
            ) : (
                sortableContent
            )}

            {showFooterCount && items.length > 0 && (
                <p className="text-sm text-muted-foreground">
                    Total {items.length} varian
                </p>
            )}
        </div>
    );
}
