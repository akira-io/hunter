import { Breadcrumbs } from '@/components/breadcrumbs';
import SearchHunt from '@/components/feed/SearchHunt';
import { NavUser } from '@/components/nav-user';
import { NotificationBell } from '@/components/notifications/NotificationBell';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { type BreadcrumbItem as BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/react';

export function AppSidebarHeader({ breadcrumbs = [] }: { breadcrumbs?: BreadcrumbItemType[] }) {
    const { auth } = usePage<{ auth: { user?: { id: number } } }>().props;

    return (
        <header className="bg-card border-sidebar-border/50 fixed z-50 flex h-16 w-full shrink-0 items-center border-b transition-[width] ease-linear">
            {/* Left side - Navigation */}
            <div className="flex items-center gap-2 px-3 md:px-6">
                <SidebarTrigger className="-ml-1" />
                <div className="hidden sm:block">
                    <Breadcrumbs breadcrumbs={breadcrumbs} />
                </div>
            </div>

            {/* Spacer */}
            <div className="flex-1"></div>

            {/* Right side - Actions (Fixed position to avoid sidebar push) */}
            <div className="fixed right-3 top-0 flex h-16 items-center gap-2 md:right-6">
                {/* Mobile search button */}
                <div className="block md:hidden">
                    <SearchHunt />
                </div>

                {/* Desktop search */}
                <div className="hidden max-w-md w-full md:block">
                    <SearchHunt />
                </div>

                {auth.user && (
                    <NotificationBell currentUserId={auth.user.id} />
                )}

                <NavUser />
            </div>
        </header>
    );
}
