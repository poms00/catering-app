import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';

import AppCardMenu from '@/components/menu/app-card-menu';

import { Button } from '@/components/ui/button';
import menuRoutes from '@/routes/menu';

import type { MenuItem } from '@/types/menu/types';

interface MenuProps {
    menuItems: MenuItem[];
}

function groupMenuItems(items: MenuItem[]): MenuItem[][] {
    const groupedItems = new Map<number, MenuItem[]>();

    items.forEach((item) => {
        const key = item.menu_group_id ?? item.id;

        if (!groupedItems.has(key)) {
            groupedItems.set(key, []);
        }

        groupedItems.get(key)?.push(item);
    });

    return Array.from(groupedItems.values());
}

function buildDetailUrl(item: MenuItem): string {
    if (item.menu_group_id) {
        return menuRoutes.show.url(item.menu_group_id);
    }

    return menuRoutes.show.url(item.id, {
        query: {
            type: 'item',
        },
    });
}

export default function Menu({ menuItems = [] }: MenuProps) {
    const groupedMenus = groupMenuItems(menuItems);

    return (
        <>
            <Head title="Menu" />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl px-8 py-4">
                {/* HEADER */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-foreground">
                            Menu
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Kelola daftar menu catering Anda.
                        </p>
                    </div>

                    <Button asChild>
                        <Link href={menuRoutes.create.url()} prefetch>
                            <Plus className="mr-2 h-4 w-4" />
                            Tambah Menu
                        </Link>
                    </Button>
                </div>

                {/* GRID */}
                <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 lg:grid-cols-5">
                    {groupedMenus.length > 0 ? (
                        groupedMenus.map((group) => (
                            <AppCardMenu
                                key={group[0].menu_group_id ?? group[0].id}
                                item={group[0]}
                                items={group}
                                href={buildDetailUrl(group[0])}
                            />
                        ))
                    ) : (
                        <p className="col-span-full text-center text-muted-foreground">
                            Belum ada menu.
                        </p>
                    )}
                </div>
            </div>
        </>
    );
}

Menu.layout = {
    breadcrumbs: [
        {
            title: 'Menu',
            href: menuRoutes.index(),
            current: true,
        },
    ],
};
