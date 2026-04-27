import { startTransition, useEffect, useState } from 'react';
import MainImage from '@/components/menu/main-image';
import ThumbnailList from '@/components/menu/thumbnail-list';
import VariantInfo from '@/components/menu/variant-info';

import type { MenuGroup, MenuImage, MenuItem } from '@/types/menu/types';

type MenuItemVariant = MenuItem & {
    images: MenuImage[];
};

interface DetailMenuProps {
    group: MenuGroup;
}

function resolvePrimaryImage(variant: MenuItemVariant | null): string | null {
    if (variant?.images?.length) {
        return (
            variant.images.find((image) => image.is_primary)?.image_url ??
            variant.images[0]?.image_url ??
            null
        );
    }

    if (typeof variant?.primary_image === 'string') {
        return variant.primary_image;
    }

    return variant?.primary_image?.image_url ?? null;
}

function sortImages(images: MenuImage[] = []): MenuImage[] {
    return [...images].sort((a, b) => {
        if (a.is_primary === b.is_primary) {
            return a.sort_order - b.sort_order;
        }

        return a.is_primary ? -1 : 1;
    });
}

function resolveVariants(group: MenuGroup): MenuItemVariant[] {
    return (group.menu_items ?? []).map((item) => ({
        ...item,
        images: sortImages(item.images ?? []),
    }));
}

function resolveInitialVariant(
    variants: MenuItemVariant[],
): MenuItemVariant | null {
    return (
        variants.find((v) => v.is_default) ??
        variants[0] ??
        null
    );
}

function resolveGroupImage(group: MenuGroup): string | null {
    const sortedImages = sortImages(group.images ?? []);

    return (
        sortedImages.find((image) => image.is_primary)?.image_url ??
        sortedImages[0]?.image_url ??
        null
    );
}

export default function AppDetailMenu({ group }: DetailMenuProps) {
    const variants = resolveVariants(group);
    const hasMultipleVariants = variants.length > 1;
    const fallbackVariant = resolveInitialVariant(variants);

    const [activeVariant, setActiveVariant] = useState<MenuItemVariant | null>(
        null,
    );

    const displayedVariant = activeVariant ?? fallbackVariant;

    const handleSelectVariant = (variant: MenuItem | null): void => {
        if (!variant) {
            setActiveVariant(null);
            
            return;
        }

        setActiveVariant({
            ...variant,
            images: sortImages(variant.images ?? []),
        });
    };

    useEffect(() => {
        startTransition(() => {
            setActiveVariant(null);
        });
    }, [group]);

    return (
        <div className="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <div>
                <MainImage
                    imageUrl={
                        resolvePrimaryImage(displayedVariant) ??
                        resolveGroupImage(group)
                    }
                    alt={displayedVariant?.name ?? group.name}
                />
            </div>

            <div className="flex flex-col gap-4">
                {hasMultipleVariants && (
                    <div className="order-1 xl:order-2">
                        <ThumbnailList
                            variants={variants}
                            activeVariantId={activeVariant?.id ?? null}
                            onSelectVariant={handleSelectVariant}
                        />
                    </div>
                )}

                <div
                    className={`order-2 xl:order-1 ${
                        hasMultipleVariants ? 'xl:sticky xl:top-4' : ''
                    }`}
                >
                    <VariantInfo
                        variant={displayedVariant}
                        category={group.menu_category}
                        group={group}
                    />
                </div>
            </div>
        </div>
    );
}
