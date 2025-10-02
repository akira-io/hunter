import { Skeleton } from '@/components/ui/skeleton';

export function NotificationItemSkeleton() {
    return (
        <div className="flex items-start gap-4 border-b p-4 last:border-0">
            <Skeleton className="h-10 w-10 rounded-full" />
            <div className="flex-1 space-y-2">
                <Skeleton className="h-4 w-3/4" />
                <Skeleton className="h-3 w-1/2" />
                <Skeleton className="h-3 w-1/4" />
            </div>
        </div>
    );
}
