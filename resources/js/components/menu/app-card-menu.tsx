import { Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import {
    Item,
    ItemActions,
    ItemContent,
    ItemDescription,
    ItemHeader,
    ItemTitle,
} from '@/components/ui/item';
import type { MenuItem } from '@/types/menu/types';

interface CardMenuProps {
    item: MenuItem;
    items: MenuItem[];
    href: string;
}

export default function AppCardMenu({ item, items, href }: CardMenuProps) {
    const variants = items;

    const isSingle = variants.length <= 1;

    const prices = variants.map((v) => Number(v.base_price || 0));
    const fallbackPrice = Number(
        variants[0]?.base_price || item.base_price || 0,
    );

    const minPrice = prices.length ? Math.min(...prices) : fallbackPrice;

    const format = (val: number) =>
        new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        }).format(val);

    const title = isSingle
        ? (variants[0]?.name ?? item.name)
        : (item.menu_group?.name ?? item.name);

    const preview = `${variants.length} pilihan`;

    return (
        <Item className="group overflow-hidden rounded-2xl border bg-background p-3 shadow-sm transition-all  hover:shadow-md">
            <Link href={href} className="block w-full max-w-xs">
                {/* IMAGE */}
                <div className="relative aspect-[4/3] w-full overflow-hidden rounded-2xl bg-muted">
                    <ItemActions className="absolute top-2 right-2 z-20 flex items-center gap-1">
                        <Badge
                            variant={item.is_active ? 'default' : 'destructive'}
                            className="text-[10px]"
                        >
                            {item.is_active ? 'Aktif' : 'Tidak Aktif'}
                        </Badge>
                    </ItemActions>

                    {item.menu_images ? (
                        <img
                            src={item.menu_images}
                            alt={item.name}
                            className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                        />
                    ) : (
                        <div className="flex h-full items-center justify-center text-sm text-muted-foreground">
                            No Image
                        </div>
                    )}
                </div>
            </Link>

            {/* HEADER */}
            <ItemHeader>
                <div>
                    <ItemTitle className="line-clamp-1 text-sm font-medium text-base">
                        {title}
                    </ItemTitle>

                    <ItemDescription className="text-sm font-medium text-green-600">
                        {isSingle
                            ? format(minPrice)
                            : `Mulai dari ${format(minPrice)}`}
                    </ItemDescription>
                </div>
            </ItemHeader>

            {/* CONTENT */}
            <ItemContent className="px-0 pb-0">
                <div className="line-clamp-1 text-xs text-muted-foreground">
                    {isSingle ? item.menu_category?.name : preview}
                </div>
            </ItemContent>

            {/* FOOTER */}
            {/* <ItemFooter className="border-t pt-4">
                <div className="inline-flex h-9 w-full items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90">
                    Detail
                </div>
            </ItemFooter> */}
        </Item>
    );
}
