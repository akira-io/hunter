import { cn } from '@/lib/utils';
import { Progress } from '@/components/ui/progress';

interface CharacterCounterProps {

    count: number;

    max: number;

    showProgress?: boolean;

    progressPercentage?: number;

    isNearLimit?: boolean;

    isOverLimit?: boolean;

    className?: string;

    variant?: 'inline' | 'with-progress';
}

export function CharacterCounter({
    count,
    max,
    showProgress = false,
    progressPercentage = 0,
    isNearLimit = false,
    isOverLimit = false,
    className,
    variant = 'inline',
}: CharacterCounterProps) {
    if (variant === 'with-progress') {
        return (
            <div className={cn('w-full space-y-2', className)}>
                <div className="flex items-center gap-3">
                    <Progress
                        value={progressPercentage}
                        className={cn(
                            'h-1 flex-1 transition-all duration-300',
                            isOverLimit && 'bg-red-200 dark:bg-red-950',
                            isNearLimit &&
                                !isOverLimit &&
                                'bg-amber-200 dark:bg-amber-950',
                        )}
                        indicatorClassName={cn(
                            isOverLimit && 'bg-red-500',
                            isNearLimit && !isOverLimit && 'bg-amber-500',
                            !isNearLimit && 'bg-purple-500',
                        )}
                    />
                    <span
                        className={cn(
                            'text-xs font-medium transition-colors sm:text-sm',
                            isOverLimit && 'text-red-500',
                            isNearLimit &&
                                !isOverLimit &&
                                'text-amber-500 dark:text-amber-400',
                            !isNearLimit &&
                                !isOverLimit &&
                                'text-muted-foreground',
                        )}
                    >
                        {count}/{max}
                    </span>
                </div>
            </div>
        );
    }

    return (
        <div className={cn('flex items-center justify-end', className)}>
            {showProgress && (
                <Progress
                    value={progressPercentage}
                    className={cn(
                        'mr-3 h-1 w-20 transition-all duration-300',
                        isOverLimit && 'bg-red-200 dark:bg-red-950',
                        isNearLimit &&
                            !isOverLimit &&
                            'bg-amber-200 dark:bg-amber-950',
                    )}
                    indicatorClassName={cn(
                        isOverLimit && 'bg-red-500',
                        isNearLimit && !isOverLimit && 'bg-amber-500',
                        !isNearLimit && 'bg-purple-500',
                    )}
                />
            )}
            <span
                className={cn(
                    'text-xs font-medium transition-colors sm:text-sm',
                    isOverLimit && 'text-red-500',
                    isNearLimit &&
                        !isOverLimit &&
                        'text-amber-500 dark:text-amber-400',
                    !isNearLimit && !isOverLimit && 'text-muted-foreground',
                )}
            >
                {count}/{max}
            </span>
        </div>
    );
}
