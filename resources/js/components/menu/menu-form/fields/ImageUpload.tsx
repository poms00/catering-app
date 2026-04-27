import { ImageIcon, Trash2, Upload } from 'lucide-react';
import { useRef } from 'react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

interface ImageUploadProps {
    preview: string | null;
    onChange: (file: File) => void;
    onRemove: () => void;
    disabled?: boolean;
}

export default function ImageUpload({
    preview,
    onChange,
    onRemove,
    disabled = false,
}: ImageUploadProps) {
    const inputRef = useRef<HTMLInputElement>(null);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];

        if (file && file.type.startsWith('image/')) {
            onChange(file);
        }
    };

    return (
        <div className="flex flex-col items-center gap-1.5">
            {/* Upload area */}
            <button
                type="button"
                onClick={() => !disabled && inputRef.current?.click()}
                disabled={disabled}
                className={cn(
                    'group relative flex h-[200px] w-[200px] shrink-0 cursor-pointer items-center justify-center overflow-hidden rounded-lg border border-dashed border-border/80 bg-muted/30 transition-colors',
                    'hover:border-primary/50 hover:bg-muted/50',
                    disabled && 'cursor-not-allowed opacity-60',
                )}
            >
                {preview ? (
                    <>
                        <img
                            src={preview}
                            alt="Preview"
                            className="h-full w-full object-cover"
                        />
                        <div className="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                            <Upload className="size-8 text-white" />
                        </div>
                    </>
                ) : (
                    <div className="flex flex-col items-center gap-2 px-3 text-center">
                        <ImageIcon className="size-8 text-muted-foreground transition-colors group-hover:text-primary/70" />
                        <span className="text-[11px] leading-snug text-muted-foreground">
                            Klik untuk upload
                        </span>
                    </div>
                )}
            </button>

            <input
                ref={inputRef}
                type="file"
                accept="image/*"
                onChange={handleChange}
                className="hidden"
                disabled={disabled}
            />

            {preview ? (
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    onClick={onRemove}
                    disabled={disabled}
                    className="h-6 gap-1 px-2 text-[11px] text-muted-foreground hover:text-destructive"
                >
                    <Trash2 className="size-3" />
                    Hapus
                </Button>
            ) : (
                <span className="text-[10px] text-muted-foreground">
                    JPG, PNG · maks 1MB
                </span>
            )}
        </div>
    );
}
