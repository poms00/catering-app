import { XIcon } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import {
    Combobox,
    ComboboxChips,
    ComboboxChipsInput,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxItem,
    ComboboxList,
    useComboboxAnchor,
} from '@/components/ui/combobox';

type Option = {
    id: number;
    name: string;
};

type MenuMultiComboboxProps = {
    items: Option[];
    value?: number[];
    placeholder?: string;
    emptyText?: string;
    onValueChange?: (ids: number[]) => void;
};

export default function MenuMultiCombobox({
    items,
    value = [],
    placeholder = 'Pilih item',
    emptyText = 'Tidak ada item ditemukan.',
    onValueChange,
}: MenuMultiComboboxProps) {
    const anchorRef = useComboboxAnchor();

    // ⚡ prevent undefined issues + stable reference
    const safeValue = value ?? [];

    const selectedItems = items.filter((item) => safeValue.includes(item.id));

    const handleRemove = (id: number) => {
        const newValue = safeValue.filter((v) => v !== id);
        onValueChange?.(newValue);
    };

    const handleChange = (selected: Option[]) => {
        onValueChange?.(selected.map((s) => s.id));
    };

    return (
        <Combobox
            multiple
            items={items}
            value={selectedItems}
            itemToStringValue={(item: Option) => String(item.id)}
            itemToStringLabel={(item: Option) => item.name}
            isItemEqualToValue={(a: Option, b: Option) => a.id === b.id}
            onValueChange={handleChange}
        >
            <ComboboxChips ref={anchorRef}>
                {/* ❗ FIX: jangan pakai function children kalau API tidak support */}
                <>
                    {selectedItems.map((item) => (
                        <Badge
                            key={item.id}
                            variant="secondary"
                            className="gap-1 pr-1"
                        >
                            {item.name}
                            <button
                                type="button"
                                className="rounded-xs hover:bg-muted-foreground/20"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    handleRemove(item.id);
                                }}
                            >
                                <XIcon className="size-3" />
                            </button>
                        </Badge>
                    ))}
                </>
                <ComboboxChipsInput placeholder={placeholder} />
            </ComboboxChips>

            <ComboboxContent anchor={anchorRef}>
                <ComboboxEmpty>{emptyText}</ComboboxEmpty>
                <ComboboxList>
                    {(item: Option) => (
                        <ComboboxItem key={item.id} value={item}>
                            {item.name}
                        </ComboboxItem>
                    )}
                </ComboboxList>
            </ComboboxContent>
        </Combobox>
    );
}
