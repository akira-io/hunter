import { CreateHunt } from '@/components/feed/CreateHunt';
import { HuntCard } from '@/components/feed/HuntCard';
import { useHuntImageProcessing } from '@/hooks/use-hunt-image-processing';
import AppLayout from '@/layouts/app-layout';
import hunts from '@/routes/hunts';
import { type BreadcrumbItem, Hunt } from '@/types';
import { Head, InfiniteScroll } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Hunts',
        href: hunts.index().url,
    },
];

interface HuntLineProps {
    hunts: {
        data: Hunt[];
    };
    newHunt?: Hunt;
}

export default function HuntLine({ hunts, newHunt }: HuntLineProps) {
    // Local state to manage hunts with real-time updates
    const [localHunts, setLocalHunts] = useState<Hunt[]>(hunts.data);

    // Hook that will update hunts when images are processed
    useHuntImageProcessing(setLocalHunts);

    // Merge newHunt at the top if it exists and isn't already in the list
    const displayHunts = useMemo(() => {
        if (!newHunt) {
            return localHunts;
        }

        // Check if hunt already exists in the list
        const huntExists = localHunts.some((hunt) => hunt.id === newHunt.id);

        if (huntExists) {
            return localHunts;
        }

        // Add new hunt at the beginning
        return [newHunt, ...localHunts];
    }, [localHunts, newHunt]);

    // Update local hunts when server data changes (pagination, etc)
    useEffect(() => {
        setLocalHunts(hunts.data);
    }, [hunts.data]);

    useEffect(() => {
        if (newHunt) {
            console.log('New hunt created with status:', newHunt.image_processing_status);
        }
    }, [newHunt]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="HuntLine" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <CreateHunt />
                <InfiniteScroll data="hunts">
                    {displayHunts.map((hunt) => (
                        <HuntCard key={hunt.id} hunt={hunt} />
                    ))}
                </InfiniteScroll>
            </div>
        </AppLayout>
    );
}
