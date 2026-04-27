import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';

type Props = {
    value: string;
    onChange: (value: string) => void;
    disabled?: boolean;
};

export default function DescriptionField({ value, onChange, disabled }: Props) {
    return (
        <div className="grid gap-1.5">
            <Label htmlFor="description" className="text-xs text-muted-foreground">
                Deskripsi
            </Label>
            <Textarea
                id="description"
                rows={3}
                value={value}
                onChange={(e) => onChange(e.target.value)}
                placeholder="Deskripsi singkat untuk tim admin."
                className={cn('min-h-0 resize-none text-sm')}
                disabled={disabled}
            />
        </div>
    );
}
