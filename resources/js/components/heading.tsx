import { router } from '@inertiajs/react';
import { ChevronLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';

type HeadingAction = {
    title: string;
    href?: string;
    onClick?: () => void;
    icon?: React.ReactNode;
    variant?: 'default' | 'outline' | 'secondary' | 'destructive';
};

export default function Heading({
    title,
    description,
    variant = 'default',
    showBack = false,
    onBack,
    actions = [],
}: {
    title: string;
    description?: string;
    variant?: 'default' | 'small';
    showBack?: boolean;
    onBack?: () => void;
    actions?: HeadingAction[];
}) {
    function handleActionClick(action: HeadingAction): void {
        if (action.href) {
            if (/^https?:\/\//.test(action.href)) {
                window.location.assign(action.href);

                return;
            }

            router.visit(action.href);

            return;
        }

        action.onClick?.();
    }

    return (
        <header className={variant === 'small' ? '' : 'mb-8'}>
            <div className="flex flex-col gap-2">
                <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    {/* LEFT */}
                    <div className="flex flex-col gap-1">
                        <div className="flex items-center gap-2">
                            {showBack && (
                                <button
                                    type="button"
                                    onClick={onBack}
                                    className="-ml-2 rounded-md p-2 transition hover:bg-muted active:scale-95"
                                >
                                    <ChevronLeft className="h-5 w-5" />
                                </button>
                            )}

                            <h1
                                className={
                                    variant === 'small'
                                        ? 'text-sm font-medium'
                                        : 'text-2xl font-semibold text-foreground'
                                }
                            >
                                {title}
                            </h1>
                        </div>

                        {description && (
                            <p
                                className={`text-sm text-muted-foreground ${
                                    showBack ? 'pl-9' : ''
                                }`}
                            >
                                {description}
                            </p>
                        )}
                    </div>

                    {/* RIGHT */}
                    {actions.length > 0 && (
                        <div className="flex flex-wrap items-center gap-2">
                            {actions.map((action, index) => (
                                <Button
                                    key={index}
                                    type="button"
                                    variant={action.variant || 'default'}
                                    onClick={() => handleActionClick(action)}
                                >
                                    <span className="flex items-center gap-2">
                                        {action.icon && (
                                            <span className="h-4 w-4">
                                                {action.icon}
                                            </span>
                                        )}
                                        {action.title}
                                    </span>
                                </Button>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </header>
    );
}
