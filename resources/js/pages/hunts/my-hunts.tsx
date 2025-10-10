import { HuntCard } from '@/components/feed/HuntCard';
import { useHuntImageProcessing } from '@/hooks/use-hunt-image-processing';
import AppLayout from '@/layouts/app-layout';
import hunts from '@/routes/hunts';
import { type BreadcrumbItem, Hunt } from '@/types';
import { Head, InfiniteScroll } from '@inertiajs/react';
import { useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Hunts',
        href: hunts.index().url,
    },
    {
        title: 'My Hunts',
        href: hunts.my().url,
    },
];

interface MyHuntsProps {
    hunts: {
        data: Hunt[];
    };
}

export default function MyHunts({ hunts: initialHunts }: MyHuntsProps) {
    const [localHunts, setLocalHunts] = useState<Hunt[]>(initialHunts.data);

    // Hook that will update hunts when images are processed
    useHuntImageProcessing(setLocalHunts);

    // Update local hunts when server data changes (pagination, etc)
    useEffect(() => {
        setLocalHunts(initialHunts.data);
    }, [initialHunts.data]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="My Hunts" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-zinc-900 dark:text-zinc-100">My Hunts</h1>
                    <p className="text-sm text-zinc-500 dark:text-zinc-400">
                        {localHunts.length > 0 ? `${localHunts.length} hunt${localHunts.length === 1 ? '' : 's'}` : 'No hunts yet'}
                    </p>
                </div>
                {localHunts.length === 0 ? (
                    <div className="border-border bg-card flex flex-col items-center justify-center rounded-lg border p-12 text-center">
                        <p className="text-muted-foreground mb-2 text-lg font-medium">No hunts yet</p>
                        <p className="text-muted-foreground text-sm">Share your first hunt to get started! Your hunts will appear here.</p>
                    </div>
                ) : (
                    <InfiniteScroll data="hunts">
                        {localHunts.map((hunt) => (
                            <HuntCard key={hunt.id} hunt={hunt} />
                        ))}
                    </InfiniteScroll>
                )}
            </div>
        </AppLayout>
    );
}
