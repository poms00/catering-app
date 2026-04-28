import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { GripVertical, Plus, Trash2 } from 'lucide-react';
import type { ReactNode } from 'react';

import type { BuilderEntry, DragData } from '@/components/menu-builder/types';
import { entrySortableId } from '@/components/menu-builder/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type SortableEntryCardProps = {
    entry: BuilderEntry;
    children: ReactNode;
    dragDisabled?: boolean;
    onNameChange: (value: string) => void;
    onDelete: () => void;
    onAddMenu?: () => void;
};

export default function SortableEntryCard({
    entry,
    children,
    dragDisabled = false,
    onNameChange,
    onDelete,
    onAddMenu,
}: SortableEntryCardProps) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({
        id: entrySortableId(entry.id),
        data: {
            type: 'entry',
            entryId: entry.id,
        } satisfies DragData,
        disabled: dragDisabled,
    });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
    };

    const className = [
        'rounded-lg border bg-card/95 p-4 shadow-xs transition-[box-shadow,opacity,transform]',
        'hover:shadow-sm',
        isDragging ? 'opacity-60 ring-2 ring-primary/15' : '',
    ]
        .filter(Boolean)
        .join(' ');

    return (
        <section ref={setNodeRef} style={style} className={className}>
            <div className="grid gap-4 border-b pb-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-start">
                <div className="grid min-w-0 gap-3 lg:grid-cols-[auto_minmax(220px,0.8fr)_minmax(0,1fr)] lg:items-end">
                    <div className="flex items-center gap-2 lg:pb-1">
                        <button
                            {...(dragDisabled ? {} : attributes)}
                            {...(dragDisabled ? {} : listeners)}
                            className="flex h-8 w-8 shrink-0 cursor-grab items-center justify-center rounded-md border border-transparent text-muted-foreground transition-colors hover:border-border hover:bg-muted hover:text-foreground active:cursor-grabbing disabled:cursor-default disabled:opacity-50"
                            aria-label="Drag to reorder"
                            type="button"
                            disabled={dragDisabled}
                        >
                            <GripVertical className="h-4 w-4" />
                        </button>

                        <Badge variant="outline" className="shrink-0">
                            {entry.type === 'wrapper'
                                ? 'Wrapper'
                                : 'Menu Single'}
                        </Badge>
                    </div>

                    {entry.type === 'wrapper' ? (
                        <div className="space-y-1.5">
                            <Input
                                value={entry.name}
                                onChange={(event) =>
                                    onNameChange(event.target.value)
                                }
                                className="h-9"
                                placeholder="Nama wrapper"
                            />
                        </div>
                    ) : (
                        <p className="text-sm text-muted-foreground lg:col-span-2 lg:pb-2">
                            Menu ini berada di luar wrapper.
                        </p>
                    )}
                </div>

                <div className="flex items-center justify-end gap-2">
                    {entry.type === 'wrapper' && onAddMenu && (
                        <Button
                            onClick={onAddMenu}
                            size="sm"
                            className="gap-1.5"
                            type="button"
                        >
                            <Plus className="h-4 w-4" />
                            Tambah Menu
                        </Button>
                    )}

                    <Button
                        size="icon"
                        variant="ghost"
                        className="h-8 w-8 text-destructive hover:bg-destructive/10 hover:text-destructive"
                        onClick={onDelete}
                        type="button"
                    >
                        <Trash2 className="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <div className="mt-4">{children}</div>
        </section>
    );
}
