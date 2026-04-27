import type { InertiaLinkProps } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';

export type BreadcrumbItem = {
    title: string;
    href: InertiaLinkProps['href'] | null;
    current?: boolean;
};

export type NavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;

    /**
     * Optional route matcher untuk active state
     * contoh: ['/menu', '/menu/create']
     */
    match?: string[];

    /**
     * kalau mau override active manual (opsional)
     */
    isActive?: boolean;
};
