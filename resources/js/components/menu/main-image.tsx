interface MainImageProps {
    imageUrl: string | null;
    alt: string;
}

export default function MainImage({ imageUrl, alt }: MainImageProps) {
    return (
        <div className="overflow-hidden bg-muted rounded-2xl sm:border">
           <div className="aspect-[1/1] sm:aspect-[5/4] lg:aspect-[4/3] w-full">
                {imageUrl ? (
                    <img
                        src={imageUrl}
                        alt={alt}
                        className="h-full w-full object-cover"
                    />
                ) : (
                    <div className="flex h-full w-full items-center justify-center px-6 text-center text-sm text-muted-foreground">
                        Gambar menu belum tersedia
                    </div>
                )}
            </div>
        </div>
    );
}
