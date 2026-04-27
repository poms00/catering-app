import type { MenuImageItem, MenuItemVariant } from '@/types/menu';

interface ThumbnailListProps {
    variants: MenuItemVariant[];
    activeVariantId: number | null;
    onSelectVariant: (variant: MenuItemVariant | null) => void;
}

interface VariantThumbnail {
    image: MenuImageItem | null;
    variant: MenuItemVariant;
}

// 🔥 helper: flatten variant + images
function buildVariantThumbnails(
    variants: MenuItemVariant[],
): VariantThumbnail[] {
    return variants.flatMap((variant) => {
        const images = variant.images ?? [];

        if (!images.length) {
            return [{ image: null, variant }] as VariantThumbnail[];
        }

        return images.map((image) => ({
            image,
            variant,
        })) as VariantThumbnail[];
    });
}

export default function ThumbnailList({
    variants,
    activeVariantId,
    onSelectVariant,
}: ThumbnailListProps) {
    if (!variants.length) {
        return null;
    }

    // 🔥 memo ringan (biar tidak recalculation berulang)
    const thumbnails = buildVariantThumbnails(variants);

    // 🔥 header hanya berubah jika user benar-benar memilih variant
    const activeVariant =
        variants.find((v) => v.id === activeVariantId) ?? null;

    // 🔥 dynamic title (Shopee style)
    const title = activeVariant
        ? activeVariant.name
        : `${variants.length} Pilihan Menu`;

    return (
        <div className="space-y-3">
            {/* HEADER */}
            <div className="flex items-center justify-between gap-3">
                <h3 className="text-sm font-medium text-foreground">
                    {title}
                </h3>
            </div>

            {/* GRID */}
            <div className="grid grid-cols-3 gap-3 sm:grid-cols-4">
                {thumbnails.map(({ image, variant }) => {
                    const isActive = activeVariantId === variant.id;

                    return (
                        <button
                            key={`${variant.id}-${image?.id ?? 'noimg'}`}
                            type="button"
                            onClick={() => {
                                if (isActive) {
                                    onSelectVariant(null);
                                } else {
                                    onSelectVariant(variant);
                                }
                            }}
                            className={`overflow-hidden rounded-xl border bg-muted text-left transition ${
                                isActive
                                    ? 'border-primary ring-2 ring-primary/20'
                                    : 'border-border hover:border-primary/40'
                            }`}
                        >
                            {/* IMAGE */}
                            {image ? (
                                <img
                                    src={image.image_url}
                                    alt={variant.name}
                                    className="h-24 w-full object-cover"
                                />
                            ) : (
                                <div className="flex h-24 items-center justify-center bg-muted/60 text-muted-foreground">
                                    <span className="text-[11px]">
                                        Belum ada gambar
                                    </span>
                                </div>
                            )}

                        </button>
                    );
                })}
            </div>
        </div>
    );
}
