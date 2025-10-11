import { cn } from '@/lib/utils';
import { ComponentProps } from 'react';

export function SectionHeader({
    title,
    description,
    className,
    ...props
}: {
    title: string;
    description?: string;
    className?: string;
} & ComponentProps<'div'>) {
    return (
        <div
            data-slot="section-header"
            className={cn('mb-4 px-4', className)}
            {...props}
        >
            <h2 className="text-2xl font-bold">{title}</h2>
            {description && (
                <p className="prose w-full max-w-100 text-muted-foreground">
                    {description}
                </p>
            )}
        </div>
    );
}
