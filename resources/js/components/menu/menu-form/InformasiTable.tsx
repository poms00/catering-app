import {
    DndContext,
    DragOverlay,
    KeyboardSensor,
    PointerSensor,
    closestCenter,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import type { DragEndEvent, DragStartEvent } from '@dnd-kit/core';
import {
    SortableContext,
    arrayMove,
    sortableKeyboardCoordinates,
    useSortable,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { GripVertical, ImageIcon, Pencil, Plus, Trash2 } from 'lucide-react';
import { useState, useEffect } from 'react';

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';

export interface VarianMenu {
    id: number;
    name: string;
    image_url?: string | null;
    imagePreview?: string | null;
    base_price: number | string;
    is_active: boolean;
    sort_order?: number | null;
    is_default?: boolean;
}

type SortableRowProps = {
    varian: VarianMenu;
    index: number;
    isDragOverlay?: boolean;
    onToggleStatus: (varianId: number, value: boolean) => void;
    onEdit: (varianId: number, payload: Partial<VarianMenu> | null) => void;
    onDelete: (varianId: number) => void;
};

type InformasiTableProps = {
    varianList?: VarianMenu[];
    canAddMore?: boolean;
    onTambah: () => void;
    onEdit: (varianId: number, payload: Partial<VarianMenu> | null) => void;
    onReorder: (items: VarianMenu[]) => void;
    onDelete: (varianId: number) => void;
};

const formatRupiah = (value: number | string) =>
    new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value) || 0);

function SortableRow({
    varian,
    index,
    isDragOverlay = false,
    onToggleStatus,
    onEdit,
    onDelete,
}: SortableRowProps) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({ id: varian.id });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
    };

    return (
        <tr
            ref={setNodeRef}
            style={style}
            className={[
                'border-b last:border-b-0',
                isDragging && !isDragOverlay
                    ? 'bg-muted/30 opacity-40'
                    : 'bg-background',
                isDragOverlay
                    ? 'rounded-lg opacity-95 shadow-xl ring-2 ring-primary/20'
                    : '',
            ]
                .filter(Boolean)
                .join(' ')}
        >
            <td className="w-8 px-3 py-3">
                <button
                    {...attributes}
                    {...listeners}
                    className="flex cursor-grab items-center justify-center rounded p-0.5 text-muted-foreground/50 transition-colors hover:bg-muted hover:text-muted-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-primary active:cursor-grabbing"
                    aria-label="Drag to reorder"
                    type="button"
                >
                    <GripVertical className="h-4 w-4" />
                </button>
            </td>

            <td className="w-16 px-4 py-3">
                <div className="flex h-9 w-9 items-center justify-center rounded-md border bg-muted/30 text-sm font-medium tabular-nums">
                    {index + 1}
                </div>
            </td>

            <td className="w-16 px-4 py-3">
                <Avatar className="h-10 w-10 rounded-md">
                    <AvatarImage
                        src={
                            (varian.image_url || varian.imagePreview) ??
                            undefined
                        }
                        alt={varian.name}
                        className="object-cover"
                    />
                    <AvatarFallback className="rounded-md bg-muted">
                        <ImageIcon className="h-4 w-4 text-muted-foreground" />
                    </AvatarFallback>
                </Avatar>
            </td>

            <td className="px-4 py-3">
                <Input
                    value={varian.name}
                    className="h-9"
                    onChange={(event) =>
                        onEdit(varian.id, { name: event.target.value })
                    }
                />
            </td>

            <td className="w-44 px-4 py-3">
                <div className="relative">
                    <span className="absolute top-1/2 left-3 -translate-y-1/2 text-sm text-muted-foreground select-none">
                        Rp
                    </span>
                    <Input
                        value={formatRupiah(varian.base_price)}
                        className="h-9 pl-9"
                        onChange={(event) => {
                            const value = event.target.value.replace(/[^0-9]/g, '');
                            // Hanya izinkan angka dan titik/koma untuk input harga
                            onEdit(varian.id, { base_price: value });
                        }}
                    />
                </div>
            </td>

            <td className="w-20 px-4 py-3 text-center">
                <Switch
                    checked={varian.is_active}
                    onCheckedChange={(value) =>
                        onToggleStatus(varian.id, value)
                    }
                    className="data-[state=checked]:bg-primary"
                />
            </td>

            <td className="w-20 px-4 py-3">
                <div className="flex items-center gap-1">
                    <Button
                        size="icon"
                        variant="ghost"
                        className="h-8 w-8"
                        onClick={() => onEdit(varian.id, null)}
                        type="button"
                    >
                        <Pencil className="h-4 w-4" />
                    </Button>
                    <Button
                        size="icon"
                        variant="ghost"
                        className="h-8 w-8 text-destructive hover:bg-destructive/10 hover:text-destructive"
                        onClick={() => onDelete(varian.id)}
                        type="button"
                    >
                        <Trash2 className="h-4 w-4" />
                    </Button>
                </div>
            </td>
        </tr>
    );
}

export default function InformasiTable({
    varianList = [],
    canAddMore = true,
    onTambah,
    onEdit,
    onDelete,
}: InformasiTableProps) {
    const [activeId, setActiveId] = useState<number | null>(null);
    const [items, setItems] = useState<VarianMenu[]>(varianList);

    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: { distance: 6 },
        }),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        }),
    );

    useEffect(() => {
        setItems(varianList);
    }, [varianList]);

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
                sort_order: index,
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

    return (
        <div className="space-y-4">
            <div className="flex items-start justify-between">
                <div>
                    <h3 className="text-base font-semibold">Varian Menu</h3>
                    <p className="text-sm text-muted-foreground">
                        Tambah, edit, hapus, dan urutkan varian dalam grup ini.
                    </p>
                </div>
                <Button
                    onClick={onTambah}
                    size="sm"
                    className="gap-1.5"
                    type="button"
                    disabled={!canAddMore}
                >
                    <Plus className="h-4 w-4" />
                    Tambah Varian
                </Button>
            </div>

            {!canAddMore && (
                <p className="text-sm text-muted-foreground">
                    Tanpa grup hanya boleh memiliki 1 varian.
                </p>
            )}

            <div className="overflow-hidden rounded-lg border">
                <DndContext
                    sensors={sensors}
                    collisionDetection={closestCenter}
                    onDragStart={handleDragStart}
                    onDragEnd={handleDragEnd}
                    onDragCancel={handleDragCancel}
                >
                    <div className="overflow-x-auto">
                        <table className="min-w-full text-sm">
                            <thead>
                                <tr className="border-b bg-muted/40 text-left">
                                    <th className="w-8 px-3 py-3" />
                                    <th className="w-16 px-4 py-3 font-medium text-muted-foreground">
                                        Urutan
                                    </th>
                                    <th className="w-16 px-4 py-3 font-medium text-muted-foreground">
                                        Foto
                                    </th>
                                    <th className="px-4 py-3 font-medium text-muted-foreground">
                                        Nama Varian
                                    </th>
                                    <th className="w-44 px-4 py-3 font-medium text-muted-foreground">
                                        Harga Dasar
                                    </th>
                                    <th className="w-20 px-4 py-3 text-center font-medium text-muted-foreground">
                                        Status
                                    </th>
                                    <th className="w-20 px-4 py-3 font-medium text-muted-foreground">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>

                            <SortableContext
                                items={items.map((variant) => variant.id)}
                                strategy={verticalListSortingStrategy}
                            >
                                <tbody>
                                    {items.length === 0 ? (
                                        <tr>
                                            <td
                                                colSpan={7}
                                                className="py-12 text-center text-muted-foreground"
                                            >
                                                Belum ada varian. Klik{' '}
                                                <strong>Tambah Varian</strong>{' '}
                                                untuk memulai.
                                            </td>
                                        </tr>
                                    ) : (
                                        items.map((varian, index) => (
                                            <SortableRow
                                                key={varian.id}
                                                varian={varian}
                                                index={index}
                                                onToggleStatus={
                                                    handleToggleStatus
                                                }
                                                onEdit={onEdit}
                                                onDelete={onDelete}
                                            />
                                        ))
                                    )}
                                </tbody>
                            </SortableContext>
                        </table>
                    </div>

                    <DragOverlay
                        dropAnimation={{
                            duration: 180,
                            easing: 'cubic-bezier(0.18, 0.67, 0.6, 1.22)',
                        }}
                    >
                        {activeVarian ? (
                            <table className="min-w-full text-sm">
                                <tbody>
                                    <SortableRow
                                        varian={activeVarian}
                                        index={items.findIndex(
                                            (variant) =>
                                                variant.id === activeVarian.id,
                                        )}
                                        isDragOverlay
                                        onToggleStatus={() => {}}
                                        onEdit={() => {}}
                                        onDelete={() => {}}
                                    />
                                </tbody>
                            </table>
                        ) : null}
                    </DragOverlay>
                </DndContext>
            </div>

            {items.length > 0 && (
                <p className="text-sm text-muted-foreground">
                    Total {items.length} varian
                </p>
            )}
        </div>
    );
}
