import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { UserMenuContent } from '@/components/user-menu-content';
import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { ComponentProps } from 'react';

export function NavUser({ className }: ComponentProps<'ul'>) {
    const { auth } = usePage<SharedData>().props;

    return (
        <div className={cn('flex items-center', className)}>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <button className='flex items-center gap-2 rounded-lg p-2 text-sm transition-all duration-200 hover:bg-accent/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 data-[state=open]:bg-accent data-[state=open]:text-accent-foreground'>
                        <UserInfo user={auth.user} />
                        {/*<ChevronsUpDown className='size-4 text-muted-foreground transition-transform duration-200 data-[state=open]:rotate-180' />*/}
                    </button>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    className='z-[200] w-64 rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800'
                    align='end'
                    side='bottom'
                    sideOffset={8}
                    alignOffset={-16}
                    avoidCollisions={true}
                    collisionPadding={16}
                >
                    <UserMenuContent user={auth.user} />
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}
