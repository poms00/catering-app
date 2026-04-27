import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Props = {
    value: string;
    onChange: (value: string) => void;
    disabled?: boolean;
};

export default function NameField({ value, onChange, disabled }: Props) {
    return (
        <div className="grid gap-1.5">
            <Label htmlFor="name" className="text-xs text-muted-foreground">
                Nama Menu
            </Label>
            <Input
                id="name"
                value={value}
                onChange={(e) => onChange(e.target.value)}
                placeholder="Nasi Box Ayam Bakar"
                className="h-8 text-sm"
                disabled={disabled}
            />
        </div>
    );
}
