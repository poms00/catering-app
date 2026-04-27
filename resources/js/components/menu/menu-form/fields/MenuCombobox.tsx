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
    return (
        <Combobox
            items={items.map((item) => String(item.id))}
            value={value ? String(value) : undefined}
            onValueChange={(selectedValue) => {
                const selected =
                    items.find((item) => String(item.id) === selectedValue) ??
                    null;

                onValueChange?.(selected);
            }}
        >
            <ComboboxInput placeholder={placeholder} showClear />
            <ComboboxContent>
                <ComboboxEmpty>{emptyText}</ComboboxEmpty>
                <ComboboxList>
                    {(itemValue) => {
                        const item = items.find(
                            (i) => String(i.id) === itemValue,
                        );

                        if (!item) {
                            return null;
                        }

                        return (
                            <ComboboxItem key={item.id} value={String(item.id)}>
                                {item.name}
                            </ComboboxItem>
                        );
                    }}
                </ComboboxList>
            </ComboboxContent>
        </Combobox>
    );
}
