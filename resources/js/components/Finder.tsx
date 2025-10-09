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
    const uniqueUsers = users.filter((user, index, self) => index === self.findIndex((u) => u.id === user.id));

    return (
        <>
            {/* Search Bar */}
            {onSearch && (
                <div className="mt-15 mb-40 w-full max-w-2xl px-4 md:px-0">
                    <div className="relative">
                        <Search className="text-muted-foreground absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2" />
                        <input
                            type="text"
                            placeholder="Procurar hunters por nome, username, skills..."
                            onChange={onSearch}
                            autoComplete="off"
                            autoCorrect="off"
                            autoCapitalize="off"
                            spellCheck="false"
                            data-lpignore="true"
                            data-form-type="other"
                            className="placeholder:text-muted-foreground focus:border-primary focus:ring-primary w-full rounded-lg border border-zinc-200 bg-white py-3 pr-4 pl-12 text-sm transition-all outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        {isSearchLoading && (
                            <Loader className="text-muted-foreground absolute top-1/2 right-4 h-5 w-5 -translate-y-1/2 animate-spin" />
                        )}
                    </div>
                </div>
            )}

            {/* Users Grid */}
            <div className="grid w-full max-w-7xl grid-cols-1 justify-center gap-4 transition-all duration-1 sm:grid-cols-2 md:px-10 xl:grid-cols-3">
                {uniqueUsers.map((user) => (
                    <Onboarding user={user} key={`user-${user.id}`} />
                ))}
            </div>
            <ScrollDown className="bg-foreground fixed bottom-0 h-8 w-8 rounded-md text-white dark:text-zinc-900" />
        </>
    );
}
