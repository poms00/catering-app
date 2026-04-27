import { Head, router } from '@inertiajs/react';
import { SquarePen } from 'lucide-react';
import Heading from '@/components/heading';
import AppDetailMenu from '@/components/menu/app-detail-menu';
import menuRoutes from '@/routes/menu';

import type { MenuGroup } from '@/types/menu/types';

interface ShowMenuProps {
    group: MenuGroup;
    canEdit?: boolean;
}

export default function ShowMenu({
    group,
    canEdit = true,
}: ShowMenuProps) {
    return (
        <>
            <Head title="Detail Menu" />
            <div className="flex h-full flex-1 flex-col overflow-x-auto rounded-xl px-8 py-4">
                <Heading
                    title={group.name}
                    description={group.description ?? undefined}
                    showBack={true}
                    onBack={() => router.visit(menuRoutes.index().url)}
                    actions={
                        canEdit
                            ? [
                                  {
                                      title: 'Edit',
                                      href: menuRoutes.edit.url(group.id),
                                      icon: <SquarePen className="h-4 w-4" />,
                                  },
                              ]
                            : []
                    }
                />

                <AppDetailMenu group={group} />
            </div>
        </>
    );
}

ShowMenu.layout = {
    breadcrumbs: [
        {
            title: 'Menu',
            href: menuRoutes.index(),
        },
        {
            title: 'Detail',
            href: null,
            current: true,
        },
    ],
};
