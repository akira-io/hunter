import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { type User } from '@/types';
import { Link } from '@inertiajs/react';
import { LogOut, RssIcon, Settings, UserIcon } from 'lucide-react';
import hunts from '@/routes/hunts';
import profile from '@/routes/profile';
import password from '@/routes/password';
import { logout } from '@/routes';

interface UserMenuContentProps {
    user: User;
}

export function UserMenuContent({ user }: UserMenuContentProps) {
    const cleanup = useMobileNavigation();

    return (
        <>
            <DropdownMenuLabel className="p-0 font-normal">
                <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                    <UserInfo user={user} />
                    <div className="grid flex-1 text-left text-sm leading-tight">
                        <span className="truncate font-medium">{user.name}</span>
                        <span className="text-muted-foreground truncate text-xs">{user.email}</span>
                    </div>
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                <DropdownMenuItem asChild>
                    <Link className="block w-full" href={hunts.index()} as="button" prefetch onClick={cleanup}>
                        <RssIcon className="mr-2" />
                        Hunt Line
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <Link className="block w-full" href={profile.edit()} as="button" prefetch onClick={cleanup}>
                        <UserIcon className="mr-2" />
                        Perfil
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <Link className="block w-full" href={password.edit()} as="button" prefetch onClick={cleanup}>
                        <Settings className="mr-2" />
                        Minha Conta
                    </Link>
                </DropdownMenuItem>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuItem asChild>
                <Link className="block w-full" method="post" href={logout.post()} as="button" onClick={cleanup}>
                    <LogOut className="mr-2" />
                    Sair
                </Link>
            </DropdownMenuItem>
        </>
    );
}
