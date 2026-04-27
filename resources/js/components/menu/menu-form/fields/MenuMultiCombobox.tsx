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
    const selectedItems = items.filter((item) => value.includes(item.id));

    return (
        <Combobox
            multiple
            items={items}
            value={selectedItems}
            itemToStringValue={(item: Option) => String(item.id)}
            itemToStringLabel={(item: Option) => item.name}
            isItemEqualToValue={(a: Option, b: Option) => a.id === b.id}
            onValueChange={(selected: Option[]) => {
                onValueChange?.(selected.map((s) => s.id));
            }}
        >
            <ComboboxChips ref={anchorRef}>
                {(item: Option) => (
                    <Badge
                        key={item.id}
                        variant="secondary"
                        className="gap-1 pr-1"
                        data-slot="combobox-chip"
                    >
                        {item.name}
                        <button
                            type="button"
                            className="rounded-xs hover:bg-muted-foreground/20"
                            onClick={(e) => {
                                e.stopPropagation();
                                onValueChange?.(
                                    value.filter((id) => id !== item.id),
                                );
                            }}
                        >
                            <XIcon className="size-3" />
                        </button>
                    </Badge>
                )}
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
