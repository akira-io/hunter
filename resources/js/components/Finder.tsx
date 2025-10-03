import Onboarding from '@/components/Onboarding';
import { ScrollDown } from '@/components/scroll-down';
import { User } from '@/types';
import { ChangeEvent } from 'react';

interface FinderProps {
    users: User[];
    onSearch?: (e: ChangeEvent<HTMLInputElement>) => void;
    isSearchLoading?: boolean;
}

export function Finder({ users, onSearch, isSearchLoading }: FinderProps) {
    // Remove duplicatas baseado no ID
    const uniqueUsers = users.filter((user, index, self) => index === self.findIndex((u) => u.id === user.id));

    return (
        <>
            <div className="grid w-full max-w-7xl grid-cols-1 justify-center gap-4 transition-all duration-1 sm:grid-cols-2 md:px-10 xl:grid-cols-3">
                {uniqueUsers.map((user) => (
                    <Onboarding user={user} key={`user-${user.id}`} />
                ))}
            </div>
            <ScrollDown className="bg-foreground fixed bottom-0 h-8 w-8 rounded-md text-white dark:text-zinc-900" />
        </>
    );
}
