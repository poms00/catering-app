import { Badge } from '@/components/ui/badge';
import type { MenuCategorySummary, MenuGroup, MenuItemVariant } from '@/types/menu';

interface VariantInfoProps {
    variant: MenuItemVariant | null;
    category?: MenuCategorySummary | null;
    group?: MenuGroup | null;
}

const currencyFormatter = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
});

export default function VariantInfo({
    variant,
    category,
    group,
}: VariantInfoProps) {
    const categoryName = category?.name ?? 'Tanpa kategori';
    const groupName =
        variant?.menu_group_id != null ? (group?.name ?? 'Tanpa grup') : 'Tanpa grup';
    const formattedPrice = currencyFormatter.format(
        Number(variant?.base_price ?? 0) || 0,
    );

    return (
        // <CardContent className="space-y-6 rounded-2xl border bg-background p-6">
        <>
            <div className="space-y-4">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div className="space-y-2">
                        <div className="flex flex-wrap gap-2">
                            <Badge variant="outline">{categoryName}</Badge>
                            <Badge variant="secondary">{groupName}</Badge>
                            {variant?.is_default ? (
                                <Badge>Default</Badge>
                            ) : null}
                        </div>

                        <h2 className="text-3xl font-semibold tracking-tight text-foreground">
                            {variant?.name ?? 'Variant belum tersedia'}
                        </h2>
                    </div>

                    <Badge
                        variant={variant?.is_active ? 'default' : 'destructive'}
                    >
                        {variant?.is_active ? 'Aktif' : 'Tidak Aktif'}
                    </Badge>
                </div>

                <p className="text-3xl font-bold text-green-600">
                    {formattedPrice}
                </p>
                <div className="space-y-2">
                    <h3 className="text-sm font-medium text-foreground">
                        Deskripsi
                    </h3>
                    <p className="text-sm leading-6 whitespace-pre-line text-muted-foreground">
                        {variant?.description ||
                            'Belum ada deskripsi untuk variant menu ini.'}
                    </p>
                </div>
            </div>
        </>
    );
}
