import {
    Combobox,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxList,
} from '@/components/ui/combobox';

type Option = {
    id: number;
    name: string;
};

type ReusableComboboxProps = {
    items: Option[];
    value?: number | null;
    placeholder?: string;
    emptyText?: string;
    onValueChange?: (value: Option | null) => void;
};

export default function MenuCombobox({
    items,
    value,
    placeholder = 'Pilih item',
    emptyText = 'Tidak ada item ditemukan.',
    onValueChange,
}: ReusableComboboxProps) {
    const selectedItem =
        value != null
            ? (items.find((item) => item.id === value) ?? null)
            : null;

    return (
        <Combobox
            items={items}
            value={selectedItem}
            itemToStringValue={(item: Option) => String(item.id)}
            itemToStringLabel={(item: Option) => item.name}
            isItemEqualToValue={(a: Option, b: Option) => a.id === b.id}
            onValueChange={(selected: Option | null) => {
                onValueChange?.(selected);
            }}
        >
            <ComboboxInput placeholder={placeholder} showClear />
            <ComboboxContent>
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
