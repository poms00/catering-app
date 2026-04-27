import { useEffect, useState } from 'react';

export interface MenuOption {
    id: number;
    name: string;
    value: number;
    label: string;
}

export interface UseMenuOptionsProps {
    type: 'categories' | 'groups';
    search?: string;
    categoryId?: number | null;
}

export function useMenuOptions({
    type,
    search = '',
    categoryId,
}: UseMenuOptionsProps) {
    const [options, setOptions] = useState<MenuOption[]>([]);
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const fetchOptions = async () => {
            setIsLoading(true);
            setError(null);

            try {
                let url = `/api/menu-${type}`;
                const params = new URLSearchParams();

                if (search) params.append('search', search);
                if (type === 'groups' && categoryId) {
                    params.append('category_id', String(categoryId));
                }

                if (params.toString()) {
                    url += `?${params.toString()}`;
                }

                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error(`Failed to fetch ${type}`);
                }

                const json = await response.json();
                setOptions(json.data || []);
            } catch (err) {
                setError(err instanceof Error ? err.message : 'Unknown error');
                setOptions([]);
            } finally {
                setIsLoading(false);
            }
        };

        // Debounce the search
        const timer = setTimeout(() => {
            fetchOptions();
        }, 300);

        return () => clearTimeout(timer);
    }, [type, search, categoryId]);

    return {
        options,
        isLoading,
        error,
    };
}
