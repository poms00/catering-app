import { PlusIcon, XIcon } from 'lucide-react';
import { useState } from 'react';

import { Badge } from '@/components/ui/badge';
import {
    Combobox,
    ComboboxChips,
    ComboboxChipsInput,
    ComboboxCollection,
    ComboboxContent,
    ComboboxItem,
    ComboboxList,
    ComboboxSeparator,
    useComboboxAnchor,
} from '@/components/ui/combobox';
import { cn } from '@/lib/utils';

type Option = {
    id: number;
    name: string;
};

type MenuMultiComboboxProps = {
    id?: string;
    name?: string;
    items: Option[];
    value?: number[];
    placeholder?: string;
    emptyText?: string;
    allowCreate?: boolean;
    createLabel?: string;
    className?: string;
    triggerClassName?: string;
    valueClassName?: string;
    onCreateOption?: (name: string) => Option;
    onValueChange?: (ids: number[]) => void;
};

export default function MenuMultiCombobox({
    id,
    name,
    items,
    value = [],
    placeholder = 'Pilih atau buat tag kategori',

    allowCreate = false,
    createLabel = 'Tambah',
    className,
    triggerClassName,
    valueClassName,
    onCreateOption,
    onValueChange,
}: MenuMultiComboboxProps) {
    const anchorRef = useComboboxAnchor();
    const [query, setQuery] = useState('');

    const safeValue = value ?? [];
    const trimmedQuery = query.trim();

    const selectedItems = items.filter((item) => safeValue.includes(item.id));
    const alreadyExists = items.some(
        (item) => item.name.toLowerCase() === trimmedQuery.toLowerCase(),
    );
    const canCreate =
        allowCreate &&
        trimmedQuery.length > 0 &&
        !alreadyExists &&
        onCreateOption != null;

    const handleRemove = (id: number) => {
        const newValue = safeValue.filter((v) => v !== id);
        onValueChange?.(newValue);
    };

    const handleChange = (selected: Option[]) => {
        onValueChange?.(selected.map((s) => s.id));
        setQuery('');
    };

    const handleCreate = () => {
        if (!canCreate || !onCreateOption) {
            return;
        }

        const created = onCreateOption(trimmedQuery);

        onValueChange?.([...new Set([...safeValue, created.id])]);
        setQuery('');
    };

    return (
        <div className={cn('min-w-0', className)}>
            <Combobox
                multiple
                items={items}
                value={selectedItems}
                itemToStringValue={(item: Option) => String(item.id)}
                itemToStringLabel={(item: Option) => item.name}
                isItemEqualToValue={(a: Option, b: Option) => a.id === b.id}
                onValueChange={handleChange}
            >
                <ComboboxChips
                    ref={anchorRef}
                    className={cn(
                        'min-h-9 min-w-0 rounded-md bg-background transition-[border-color,box-shadow]',
                        triggerClassName,
                    )}
                >
                    <>
                        {selectedItems.map((item) => (
                            <Badge
                                key={item.id}
                                variant="secondary"
                                className="h-6 max-w-full min-w-0 gap-1 overflow-hidden rounded-md px-2 pr-1 text-[11px]"
                            >
                                <span
                                    className={cn(
                                        'block min-w-0 flex-1 truncate text-left',
                                        valueClassName,
                                    )}
                                >
                                    {item.name}
                                </span>
                                <button
                                    type="button"
                                    className="shrink-0 rounded-xs p-0.5 hover:bg-muted-foreground/20"
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
                    <ComboboxChipsInput
                        id={id}
                        name={name}
                        placeholder={placeholder}
                        value={query}
                        className="min-h-6 text-sm placeholder:text-muted-foreground/70"
                        onChange={(event) =>
                            setQuery(event.currentTarget.value)
                        }
                        onKeyDown={(event) => {
                            if (event.key !== 'Enter' || !canCreate) {
                                return;
                            }

                            event.preventDefault();
                            handleCreate();
                        }}
                    />
                </ComboboxChips>

                <ComboboxContent
                    anchor={anchorRef}
                    className="rounded-lg border bg-popover p-1 shadow-lg"
                >
                    <ComboboxList className="data-empty:p-0">
                        {canCreate && (
                            <div className="p-1">
                                <button
                                    type="button"
                                    className="flex w-full items-center gap-3 rounded-md border border-primary/15 bg-primary/5 px-3 py-2.5 text-left transition-colors outline-none hover:border-primary/25 hover:bg-primary/10"
                                    onMouseDown={(event) =>
                                        event.preventDefault()
                                    }
                                    onClick={handleCreate}
                                >
                                    <span className="flex size-8 shrink-0 items-center justify-center rounded-md bg-primary text-primary-foreground">
                                        <PlusIcon className="size-4" />
                                    </span>
                                    <span className="min-w-0 flex-1">
                                        <span className="block text-[11px] font-medium tracking-wide text-muted-foreground uppercase">
                                            {createLabel}
                                        </span>
                                        <span className="block truncate text-sm font-medium text-foreground">
                                            {trimmedQuery}
                                        </span>
                                    </span>
                                    <span className="hidden rounded border bg-background px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground sm:inline">
                                        Enter
                                    </span>
                                </button>
                                <ComboboxSeparator className="mt-2" />
                            </div>
                        )}
                        <ComboboxCollection>
                            {(item: Option) => (
                                <ComboboxItem
                                    key={item.id}
                                    value={item}
                                    className="min-w-0 rounded-md"
                                >
                                    <span
                                        className={cn(
                                            'block min-w-0 flex-1 truncate text-left',
                                            valueClassName,
                                        )}
                                    >
                                        {item.name}
                                    </span>
                                </ComboboxItem>
                            )}
                        </ComboboxCollection>
                    </ComboboxList>
                </ComboboxContent>
            </Combobox>
        </div>
    );
}
