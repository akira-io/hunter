import { SectionHeader } from '@/components/feed/SectionHeader';
import Onboarding from '@/components/Onboarding';
import { Button } from '@/components/ui/button';
import Layout from '@/layouts/app-layout';
import { User } from '@/types';
import { Head, InfiniteScroll } from '@inertiajs/react';
import { ListFilterPlusIcon } from 'lucide-react';

interface FollowersProps {
    followers: {
        data: User[];
    };
}

export default function Hunters({ followers }: FollowersProps) {
    return (
        <Layout>
            <Head title="Hunters" />
            <div className="mb-4 flex items-start justify-between px-5">
                <SectionHeader title="Hunters" description="São pessoas que acompanham o teu trabalho e encontram inspiração em ti." />
                <Button className="text-muted-forground flex h-8 w-8 cursor-pointer border-none shadow-none" variant="secondary">
                    <ListFilterPlusIcon />
                </Button>
            </div>
            <InfiniteScroll data="followers">
                <div className="mx-auto grid w-full max-w-7xl grid-cols-1 justify-center gap-4 p-5 transition-all duration-1 sm:grid-cols-2 md:px-10 xl:grid-cols-3">
                    {followers.data.map((follower) => (
                        <Onboarding user={follower} key={follower.email} />
                    ))}
                </div>
            </InfiniteScroll>
        </Layout>
    );
}
