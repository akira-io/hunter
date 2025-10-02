import { Button } from '@/components/ui/button';
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
                    <Button
                        variant="secondary"
                        className="hover:bg-accent focus-visible:ring-ring data-[state=open]:bg-accent data-[state=open]:text-accent-foreground data-[state=open]:ring-ring/20 flex w-10 items-center gap-2 rounded-full p-2 text-sm transition-all duration-200 focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none data-[state=open]:ring-2"
                    >
                        <UserInfo user={auth.user} />
                        {/*<ChevronsUpDown className='size-4 text-muted-foreground transition-transform duration-200 data-[state=open]:rotate-180' />*/}
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    className="border-border bg-card z-[200] w-64 rounded-lg border shadow-lg"
                    align="end"
                    side="bottom"
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
