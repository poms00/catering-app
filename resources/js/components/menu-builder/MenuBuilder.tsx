import {
    DndContext,
    KeyboardSensor,
    PointerSensor,
    closestCenter,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import type { DragEndEvent } from '@dnd-kit/core';
import {
    SortableContext,
    sortableKeyboardCoordinates,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { Plus } from 'lucide-react';
import type { ReactNode } from 'react';

import RootDropZone from '@/components/menu-builder/RootDropZone';
import type { BuilderEntry } from '@/components/menu-builder/types';
import { entrySortableId } from '@/components/menu-builder/types';
import { Button } from '@/components/ui/button';

type MenuBuilderProps = {
    entries: BuilderEntry[];
    allowAddWrapper?: boolean;
    allowAddSingle?: boolean;
    allowRootItems?: boolean;
    title?: string;
    description?: string;
    emptyText?: string;
    wrapperButtonLabel?: string;
    singleButtonLabel?: string;
    onAddWrapper: () => void;
    onAddSingle: () => void;
    onDragEnd: (event: DragEndEvent) => void;
    children: (entry: BuilderEntry) => ReactNode;
};

export default function MenuBuilder({
    entries,
    allowAddWrapper = true,
    allowAddSingle = true,
    allowRootItems = true,
    title = 'Builder Menu',
    description = 'Susun wrapper dan menu single, lalu tarik menu untuk memindahkan posisinya.',
    emptyText = 'Belum ada Grup atau Menu. Gunakan tombol di atas untuk mulai menyusun daftar menu.',
    wrapperButtonLabel = 'Tambah Grup',
    singleButtonLabel = 'Tambah Menu',
    onAddWrapper,
    onAddSingle,
    onDragEnd,
    children,
}: MenuBuilderProps) {
    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: { distance: 6 },
        }),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        }),
    );
    const hasBuilderActions = allowAddWrapper || allowAddSingle;

    return (
        <div className="space-y-5">
            <section className="rounded-lg border bg-background/80 p-4 shadow-xs">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div className="space-y-1">
                        <h2 className="text-sm font-semibold tracking-wider text-muted-foreground uppercase">
                            {title}
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            {description}
                        </p>
                    </div>

                    {hasBuilderActions && (
                        <div className="flex flex-wrap gap-2">
                            {allowAddWrapper && (
                                <Button
                                    onClick={onAddWrapper}
                                    type="button"
                                    className="gap-1.5"
                                >
                                    <Plus className="h-4 w-4" />
                                    {wrapperButtonLabel}
                                </Button>
                            )}
                            {allowAddSingle && (
                                <Button
                                    onClick={onAddSingle}
                                    type="button"
                                    variant="outline"
                                    className="gap-1.5"
                                >
                                    <Plus className="h-4 w-4" />
                                    {singleButtonLabel}
                                </Button>
                            )}
                        </div>
                    )}
                </div>
            </section>

            {entries.length === 0 ? (
                <div className="rounded-lg border border-dashed bg-muted/20 py-16 text-center text-sm text-muted-foreground">
                    {emptyText}
                </div>
            ) : (
                <DndContext
                    sensors={sensors}
                    collisionDetection={closestCenter}
                    onDragEnd={onDragEnd}
                >
                    <SortableContext
                        items={entries
                            .filter((entry) => entry.type === 'wrapper')
                            .map((entry) => entrySortableId(entry.id))}
                        strategy={verticalListSortingStrategy}
                    >
                        <RootDropZone enabled={allowRootItems}>
                            {entries.map((entry) => children(entry))}
                        </RootDropZone>
                    </SortableContext>
                </DndContext>
            )}
        </div>
    );
}
