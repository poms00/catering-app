import type { ReactNode } from 'react';

type PackageMenuBuilderProps = {
    title?: string;
    description?: string;
    children: ReactNode;
};

export default function PackageMenuBuilder({
    title = 'Builder Menu Paket',
    description = 'Susun item paket dengan struktur yang bisa memakai komponen builder yang sama.',
    children,
}: PackageMenuBuilderProps) {
    return (
        <div className="space-y-5">
            <section className="rounded-lg border bg-background/80 p-4 shadow-xs">
                <div className="space-y-1">
                    <h2 className="text-sm font-semibold tracking-wider text-muted-foreground uppercase">
                        {title}
                    </h2>
                    <p className="text-sm text-muted-foreground">
                        {description}
                    </p>
                </div>
            </section>

            {children}
        </div>
    );
}
