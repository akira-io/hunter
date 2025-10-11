import Onboarding from '@/components/Onboarding';
import { ScrollDown } from '@/components/scroll-down';
import { User } from '@/types';
import { Loader, Search } from 'lucide-react';
import { ChangeEvent } from 'react';

interface FinderProps {
    users: User[];
    onSearch?: (e: ChangeEvent<HTMLInputElement>) => void;
    isSearchLoading?: boolean;
}

export function Finder({ users, onSearch, isSearchLoading }: FinderProps) {
    // Remove duplicatas baseado no ID
    const uniqueUsers = users.filter(
        (user, index, self) =>
            index === self.findIndex((u) => u.id === user.id),
    );

    return (
        <>
            {/* Search Bar */}
            {onSearch && (
                <div className="mt-15 mb-10 w-full max-w-2xl px-4 px-6 md:px-0">
                    <div className="relative">
                        <Search className="absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-muted-foreground" />
                        <input
                            type="search"
                            name="hunter_search"
                            id="hunter-search-input"
                            placeholder="Procurar hunters por nome, username, skills..."
                            onChange={onSearch}
                            autoComplete="off"
                            autoCorrect="off"
                            autoCapitalize="off"
                            spellCheck="false"
                            data-lpignore="true"
                            data-form-type="other"
                            data-1p-ignore="true"
                            role="searchbox"
                            aria-label="Procurar hunters"
                            className="w-full rounded-lg border border-zinc-200 bg-white py-3 pr-4 pl-12 text-base transition-all outline-none placeholder:text-muted-foreground focus:border-primary focus:ring-primary dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        {isSearchLoading && (
                            <Loader className="absolute top-1/2 right-4 h-5 w-5 -translate-y-1/2 animate-spin text-muted-foreground" />
                        )}
                    </div>
                </div>
            )}

            {/* Users Grid */}
            <div className="grid w-full max-w-7xl grid-cols-1 justify-center gap-4 px-6 transition-all duration-1 sm:grid-cols-2 md:px-10 xl:grid-cols-3">
                {uniqueUsers.map((user) => (
                    <Onboarding user={user} key={`user-${user.id}`} />
                ))}
            </div>
            <ScrollDown className="fixed bottom-0 h-8 w-8 rounded-md bg-foreground text-white dark:text-zinc-900" />
        </>
    );
}
