import { SectionHeader } from '@/components/feed/SectionHeader';
import { Finder as FinderComponent } from '@/components/Finder';
import { Button } from '@/components/ui/button';
import Layout from '@/layouts/app-layout';
import finder from '@/routes/finder';
import { type BreadcrumbItem, User } from '@/types';
import { Head, router } from '@inertiajs/react';
import { ListFilterPlusIcon } from 'lucide-react';
import React, { useState } from 'react';

interface FinderProps {
    users: User[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Explorar',
        href: '/finder',
    },
];
export default function Finder({ users }: FinderProps) {
    const [isSearchLoading, setIsSearchLoading] = useState(false);

    function search(e: React.ChangeEvent<HTMLInputElement>) {
        e.preventDefault();
        setIsSearchLoading(true);
        router.get(
            finder.index().url,
            { q: e.target.value },
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => {
                    setIsSearchLoading(false);
                },
            },
        );
    }

    return (
        <Layout breadcrumbs={breadcrumbs}>
            <Head title="Finder" />
            <div className="mb-4 flex items-start justify-between px-5">
                <SectionHeader title="Finder" description="Explore os perfis dos Hunters e acompanhe as suas conquistas." />
                <Button className="text-muted-forground flex h-8 w-8 cursor-pointer border-none shadow-none" variant="secondary">
                    <ListFilterPlusIcon />
                </Button>
            </div>
            <div className="mb-50 flex w-full flex-col items-center justify-start px-4 opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
                <FinderComponent users={users} onSearch={search} isSearchLoading={isSearchLoading} />
            </div>
        </Layout>
    );
}
