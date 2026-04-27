import { Badge } from '@/components/ui/badge';
import type { MenuItemVariant } from '@/types/menu';

interface VariantTableProps {
    variants: MenuItemVariant[];
    activeVariantId: number | null;
    onSelectVariant: (variant: MenuItemVariant) => void;
}

const currencyFormatter = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
});

export default function VariantTable({
    variants,
    activeVariantId,
    onSelectVariant,
}: VariantTableProps) {
    return (
        <div className="rounded-2xl border bg-background p-5">
            <div className="mb-4 flex items-center justify-between gap-3">
                <div className="space-y-1">
                    <h3 className="text-xl font-semibold text-foreground">
                        Varian / Opsi
                    </h3>
                    <p className="text-sm text-muted-foreground">
                        Klik baris atau gambar untuk mengganti variant aktif.
                    </p>
                </div>

                <Badge variant="outline">{variants.length} varian</Badge>
            </div>

            <div className="overflow-hidden rounded-xl border">
                <div className="overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-muted/60 text-left">
                            <tr className="border-b">
                                <th className="px-4 py-3 font-medium">No</th>
                                <th className="px-4 py-3 font-medium">
                                    Nama Varian
                                </th>
                                <th className="px-4 py-3 font-medium">Harga</th>
                                <th className="px-4 py-3 font-medium">
                                    Default
                                </th>
                                <th className="px-4 py-3 font-medium">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {variants.map((variant, index) => {
                                const isActive = activeVariantId === variant.id;

                                return (
                                    <tr
                                        key={variant.id}
                                        className={`cursor-pointer border-b transition last:border-b-0 ${
                                            isActive
                                                ? 'bg-primary/5'
                                                : 'hover:bg-muted/40'
                                        }`}
                                        onClick={() => onSelectVariant(variant)}
                                    >
                                        <td className="px-4 py-3 align-middle">
                                            {index + 1}
                                        </td>
                                        <td className="px-4 py-3 align-middle font-medium text-foreground">
                                            {variant.name}
                                        </td>
                                        <td className="px-4 py-3 align-middle">
                                            {currencyFormatter.format(
                                                Number(variant.base_price) || 0,
                                            )}
                                        </td>
                                        <td className="px-4 py-3 align-middle">
                                            {variant.is_default ? (
                                                <Badge>Default</Badge>
                                            ) : (
                                                <span className="text-muted-foreground">
                                                    -
                                                </span>
                                            )}
                                        </td>
                                        <td className="px-4 py-3 align-middle">
                                            <Badge
                                                variant={
                                                    variant.is_active
                                                        ? 'secondary'
                                                        : 'destructive'
                                                }
                                            >
                                                {variant.is_active
                                                    ? 'Aktif'
                                                    : 'Tidak Aktif'}
                                            </Badge>
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
}
