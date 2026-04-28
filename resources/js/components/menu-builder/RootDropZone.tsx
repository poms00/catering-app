import { useDroppable } from '@dnd-kit/core';
import type { ReactNode } from 'react';

import { ROOT_CONTAINER_ID } from '@/components/menu-builder/types';
import type { DragData } from '@/components/menu-builder/types';

type RootDropZoneProps = {
    children: ReactNode;
    enabled?: boolean;
};

export default function RootDropZone({
    children,
    enabled = true,
}: RootDropZoneProps) {
    const { isOver, setNodeRef } = useDroppable({
        id: ROOT_CONTAINER_ID,
        data: {
            type: 'container',
            entryId: null,
        } satisfies DragData,
        disabled: !enabled,
    });

    return (
        <div
            ref={setNodeRef}
            className={[
                'space-y-4 rounded-2xl border border-dashed border-transparent p-1 transition-colors',
                enabled && isOver ? 'border-primary/30 bg-primary/5' : '',
            ]
                .filter(Boolean)
                .join(' ')}
        >
            {children}
        </div>
    );
}
