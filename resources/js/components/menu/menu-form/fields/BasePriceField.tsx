import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Props = {
    value: string;
    onChange: (value: string) => void;
    disabled?: boolean;
};

export default function BasePriceField({ value, onChange, disabled }: Props) {
    return (
        <div className="grid gap-1.5">
            <Label htmlFor="base_price" className="text-xs text-muted-foreground">
                Harga Dasar
            </Label>
            <div className="relative">
                <span className="pointer-events-none absolute top-1/2 left-2.5 -translate-y-1/2 text-xs text-muted-foreground">
                    Rp
                </span>
                <Input
                    id="base_price"
                    type="number"
                    min="0"
                    step="0.01"
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
                    placeholder="35000"
                    className="h-8 pl-8 text-sm"
                    disabled={disabled}
                />
            </div>
        </div>
    );
}
