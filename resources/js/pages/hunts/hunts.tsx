import { CreateHunt } from '@/components/feed/CreateHunt';
import { HuntCard } from '@/components/feed/HuntCard';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, Hunt } from '@/types';
import { Head, InfiniteScroll } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Hunt Line',
        href: '/feed',
    },
];

interface HuntLineProps {
    hunts: {
        data: Hunt[];
    };
}

export default function HuntLine({ hunts }: HuntLineProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="HuntLine" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <CreateHunt />
                <InfiniteScroll data="hunts">
                    {hunts.data.map((hunt) => (
                        <HuntCard key={hunt.id} hunt={hunt} />
                    ))}
                </InfiniteScroll>
            </div>
        </AppLayout>
    );
}
