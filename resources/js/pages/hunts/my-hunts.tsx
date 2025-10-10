import { HuntCard } from '@/components/feed/HuntCard';
import { SectionHeader } from '@/components/feed/SectionHeader';
import { Button } from '@/components/ui/button';
import { useHuntImageProcessing } from '@/hooks/use-hunt-image-processing';
import AppLayout from '@/layouts/app-layout';
import hunts from '@/routes/hunts';
import { type BreadcrumbItem, Hunt } from '@/types';
import { Head, InfiniteScroll } from '@inertiajs/react';
import { ListFilterPlusIcon } from 'lucide-react';
import { useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Hunts',
        href: hunts.index().url,
    },
    {
        title: 'Meus Hunts',
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

    useHuntImageProcessing(setLocalHunts);

    useEffect(() => {
        setLocalHunts(initialHunts.data);
    }, [initialHunts.data]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="My Hunts" />
            <div className="mb-4 flex items-start justify-between px-5">
                <SectionHeader title="Meus Hunts" description="Acompanhe todas as suas conquistas e compartilhamentos." />
                <Button className="text-muted-forground flex h-8 w-8 cursor-pointer border-none shadow-none" variant="secondary">
                    <ListFilterPlusIcon />
                </Button>
            </div>
            <div className="mb-50 flex w-full flex-col items-center justify-start px-4 opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
                {localHunts.length === 0 ? (
                    <div className="border-border bg-card flex flex-col items-center justify-center rounded-lg border p-12 text-center">
                        <p className="text-muted-foreground mb-2 text-lg font-medium">Nenhuma hunt ainda</p>
                        <p className="text-muted-foreground text-sm">Compartilhe sua primeira hunt para começar! Suas hunts aparecerão aqui.</p>
                    </div>
                ) : (
                    <InfiniteScroll data="hunts" className="w-full">
                        {localHunts.map((hunt) => (
                            <HuntCard key={hunt.id} hunt={hunt} />
                        ))}
                    </InfiniteScroll>
                )}
            </div>
        </AppLayout>
    );
}
