import Onboarding from '@/components/Onboarding';
import { ScrollDown } from '@/components/scroll-down';
import { User } from '@/types';
import { Input } from '@headlessui/react';
import { Loader, SearchIcon } from 'lucide-react';
import { ChangeEvent } from 'react';

interface FinderProps {
    users: User[];
    onSearch: (event: ChangeEvent<HTMLInputElement>) => void;
    isSearchLoading: boolean;
}

export function Finder({ users, onSearch, isSearchLoading }: FinderProps) {
    return (
        <>
            <div className="my-10 flex w-full max-w-xl flex-col items-center justify-center dark:text-white">
                <form className="relative mb-10 w-full md:mb-20">
                    <Input
                        id="search"
                        className="peer placeholder:text-muted-foreground dark:placeholder:text-muted h-12 w-full rounded-md border border-black bg-transparent px-4 px-9 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        placeholder="Procurar por nome, email, skills..."
                        type="text"
                        name="query"
                        onChange={(e) => onSearch(e)}
                    />
                    <div className="text-muted-foreground pointer-events-none absolute inset-y-0 start-0 flex items-center justify-center ps-3 peer-disabled:opacity-50">
                        {!isSearchLoading ? <SearchIcon size={16} /> : <Loader className="animate-spin" size={16} />}
                    </div>
                </form>
            </div>
            <div className="grid w-full max-w-7xl grid-cols-1 justify-center gap-4 transition-all duration-1 sm:grid-cols-2 md:px-10 xl:grid-cols-3">
                {users.map((user) => (
                    <Onboarding user={user} key={`${user.id}-${user.email}`} />
                ))}
            </div>
            <ScrollDown className="bg-foreground fixed bottom-0 h-8 w-8 rounded-md text-white dark:text-zinc-900" />
        </>
    );
}
