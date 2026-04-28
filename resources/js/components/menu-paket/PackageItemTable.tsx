import { Trash2 } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export type PackageItem = {
    id: number;
    name: string;
    quantity: number | string;
    note?: string | null;
};

type PackageItemTableProps = {
    items?: PackageItem[];
    onEdit?: (itemId: number, payload: Partial<PackageItem>) => void;
    onDelete?: (itemId: number) => void;
};

export default function PackageItemTable({
    items = [],
    onEdit,
    onDelete,
}: PackageItemTableProps) {
    return (
        <div className="space-y-3">
            {items.length === 0 ? (
                <div className="rounded-lg border border-dashed bg-muted/20 py-10 text-center text-sm text-muted-foreground">
                    Belum ada item paket.
                </div>
            ) : (
                items.map((item) => (
                    <div
                        key={item.id}
                        className="grid gap-3 rounded-lg border bg-background/95 p-4 shadow-xs sm:grid-cols-[minmax(0,1fr)_8rem_auto] sm:items-end"
                    >
                        <div className="min-w-0 space-y-1.5">
                            <Label className="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">
                                Menu
                            </Label>
                            <Input
                                value={item.name}
                                className="h-9"
                                onChange={(event) =>
                                    onEdit?.(item.id, {
                                        name: event.target.value,
                                    })
                                }
                            />
                        </div>

                        <div className="space-y-1.5">
                            <Label className="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">
                                Qty
                            </Label>
                            <Input
                                value={item.quantity}
                                className="h-9"
                                onChange={(event) =>
                                    onEdit?.(item.id, {
                                        quantity: event.target.value.replace(
                                            /[^0-9]/g,
                                            '',
                                        ),
                                    })
                                }
                            />
                        </div>

                        <Button
                            size="icon"
                            variant="ghost"
                            className="h-9 w-9 text-destructive hover:bg-destructive/10 hover:text-destructive"
                            onClick={() => onDelete?.(item.id)}
                            type="button"
                            aria-label="Hapus item paket"
                        >
                            <Trash2 className="h-4 w-4" />
                        </Button>
                    </div>
                ))
            )}
        </div>
    );
}
