import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';

import AppCardMenu from '@/components/menu/app-card-menu';
import { Badge } from '@/components/ui/badge';
import {
    Item,
    ItemActions,
    ItemContent,
    ItemDescription,
    ItemHeader,
    ItemTitle,
} from '@/components/ui/item';

import { Button } from '@/components/ui/button';
import menuRoutes from '@/routes/menu';

import type { MenuGroupSummary, MenuItem } from '@/types/menu/types';

interface MenuProps {
    menuItems: MenuItem[];
    menuGroups: MenuGroupSummary[];
}

function hasRenderableGroup(item: MenuItem): boolean {
    return item.menu_group != null;
}

function groupMenuItems(items: MenuItem[]): MenuItem[][] {
    const groupedItems = new Map<number, MenuItem[]>();

    items.forEach((item) => {
        const key = hasRenderableGroup(item)
            ? item.menu_group!.id
            : item.id;

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

function buildEmptyGroupCards(menuGroups: MenuGroupSummary[]): MenuGroupSummary[] {
    return menuGroups.filter((group) => (group.items_count ?? 0) === 0);
}

function EmptyGroupCard({ group }: { group: MenuGroupSummary }) {
    return (
        <Item className="group overflow-hidden rounded-2xl border bg-background p-3 shadow-sm transition-all hover:shadow-md">
            <Link
                href={menuRoutes.show.url(group.id)}
                className="block w-full max-w-xs"
            >
                <div className="relative aspect-[4/3] w-full overflow-hidden rounded-2xl bg-muted">
                    <ItemActions className="absolute top-2 right-2 z-20 flex items-center gap-1">
                        <Badge
                            variant={group.is_active ? 'default' : 'destructive'}
                            className="text-[10px]"
                        >
                            {group.is_active ? 'Aktif' : 'Tidak Aktif'}
                        </Badge>
                    </ItemActions>

                    <div className="flex h-full items-center justify-center px-4 text-center text-sm text-muted-foreground">
                        Grup belum memiliki menu
                    </div>
                </div>
            </Link>

            <ItemHeader>
                <div>
                    <ItemTitle className="line-clamp-1 text-sm font-medium text-base">
                        {group.name}
                    </ItemTitle>
                    <ItemDescription className="text-sm font-medium text-muted-foreground">
                        Belum ada varian
                    </ItemDescription>
                </div>
            </ItemHeader>

            <ItemContent className="px-0 pb-0">
                <div className="line-clamp-1 text-xs text-muted-foreground">
                    Tidak ada item dalam grup ini.
                </div>
            </ItemContent>
        </Item>
    );
}

export default function Menu({ menuItems = [], menuGroups = [] }: MenuProps) {
    const groupedMenus = groupMenuItems(menuItems);
    const emptyGroups = buildEmptyGroupCards(menuGroups);
    const hasCards = groupedMenus.length > 0 || emptyGroups.length > 0;

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
                    {hasCards ? (
                        <>
                            {groupedMenus.map((group) => (
                                <AppCardMenu
                                    key={group[0].menu_group_id ?? group[0].id}
                                    item={group[0]}
                                    items={group}
                                    href={buildDetailUrl(group[0])}
                                />
                            ))}
                            {emptyGroups.map((group) => (
                                <EmptyGroupCard
                                    key={`empty-group-${group.id}`}
                                    group={group}
                                />
                            ))}
                        </>
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
