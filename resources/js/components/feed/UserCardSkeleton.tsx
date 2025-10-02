import { Skeleton } from '@/components/ui/skeleton';

export function UserCardSkeleton() {
    return (
        <div className="bg-card rounded-lg border p-6">
            <div className="flex flex-col items-center space-y-4">
                <Skeleton className="h-20 w-20 rounded-full" />
                <div className="space-y-2 text-center">
                    <Skeleton className="h-5 w-32" />
                    <Skeleton className="h-4 w-24" />
                </div>
                <Skeleton className="h-4 w-full" />
                <Skeleton className="h-4 w-full" />
                <Skeleton className="h-10 w-full" />
            </div>
        </div>
    );
}
