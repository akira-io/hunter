import { DropdownMenuItem } from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { logout } from '@/routes';
import hunts from '@/routes/hunts';
import password from '@/routes/password';
import profile from '@/routes/profile';
import { type User } from '@/types';
import { Link } from '@inertiajs/react';
import { LogOut, RssIcon, Settings, UserIcon } from 'lucide-react';

interface UserMenuContentProps {
    user: User;
}

export function UserMenuContent({ user }: UserMenuContentProps) {
    const cleanup = useMobileNavigation();

    return (
        <div className="divide-y divide-zinc-200 dark:divide-zinc-700">
            {/* User Info Section */}
            <div className="px-3 py-3 sm:px-4 sm:py-3">
                <div className="flex items-center gap-2 text-left text-sm">
                    <UserInfo user={user} />
                    <div className="grid flex-1 text-left text-sm leading-tight">
                        <span className="truncate font-semibold text-zinc-900 dark:text-zinc-100">{user.name}</span>
                        <span className="truncate text-xs text-zinc-500 dark:text-zinc-400">{user.email}</span>
                    </div>
                </div>
            </div>
            {/* Account Section */}
            <div className="py-1">
                <DropdownMenuItem asChild>
                    <Link className="block w-full px-3 py-2 sm:px-4 sm:py-2" href={hunts.index()} as="button" prefetch onClick={cleanup}>
                        <RssIcon className="mr-2 size-4 text-zinc-500 dark:text-zinc-400" />
                        <span className="text-zinc-900 dark:text-zinc-100">Hunt Line</span>
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <Link className="block w-full px-3 py-2 sm:px-4 sm:py-2" href={profile.edit()} as="button" prefetch onClick={cleanup}>
                        <UserIcon className="mr-2 size-4 text-zinc-500 dark:text-zinc-400" />
                        <span className="text-zinc-900 dark:text-zinc-100">Perfil</span>
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <Link className="block w-full px-3 py-2 sm:px-4 sm:py-2" href={password.edit()} as="button" prefetch onClick={cleanup}>
                        <Settings className="mr-2 size-4 text-zinc-500 dark:text-zinc-400" />
                        <span className="text-zinc-900 dark:text-zinc-100">Minha Conta</span>
                    </Link>
                </DropdownMenuItem>
            </div>
            {/* Logout Section */}
            <div className="py-1">
                <DropdownMenuItem asChild>
                    <Link
                        className="block w-full px-3 py-2 text-red-600 sm:px-4 sm:py-2 dark:text-red-400"
                        method="post"
                        href={logout.post()}
                        as="button"
                        onClick={cleanup}
                    >
                        <LogOut className="mr-2 size-4" />
                        <span>Sair</span>
                    </Link>
                </DropdownMenuItem>
            </div>
        </div>
    );
}
