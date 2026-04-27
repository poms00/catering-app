import { Checkbox } from '@/components/ui/checkbox';

type Props = {
    isDefault: boolean;
    isActive: boolean;
    onDefaultChange: (value: boolean) => void;
    onActiveChange: (value: boolean) => void;
    disabled?: boolean;
};

export default function StatusCheckboxes({
    isDefault,
    isActive,
    onDefaultChange,
    onActiveChange,
    disabled,
}: Props) {
    return (
        <div className="flex items-center gap-4">
            <label className="flex cursor-pointer items-center gap-1.5">
                <Checkbox
                    checked={isDefault}
                    onCheckedChange={(v) => onDefaultChange(v === true)}
                    className="h-3.5 w-3.5"
                    disabled={disabled}
                />
                <span className="text-xs text-muted-foreground">Default</span>
            </label>

            <label className="flex cursor-pointer items-center gap-1.5">
                <Checkbox
                    checked={isActive}
                    onCheckedChange={(v) => onActiveChange(v === true)}
                    className="h-3.5 w-3.5"
                    disabled={disabled}
                />
                <span className="text-xs text-muted-foreground">Aktif</span>
            </label>
        </div>
    );
}
