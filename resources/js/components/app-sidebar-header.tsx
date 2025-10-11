import { Breadcrumbs } from '@/components/breadcrumbs';
import { GlobalSearch } from '@/components/GlobalSearch';
import { NavUser } from '@/components/nav-user';
import { NotificationBell } from '@/components/notifications/NotificationBell';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { type BreadcrumbItem as BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/react';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    const { auth } = usePage<{ auth: { user?: { id: number } } }>().props;

    return (
        <header className="pwa-header gradient sticky top-0 z-50 flex min-h-[64px] w-full shrink-0 items-center border-b border-sidebar-border/50 bg-card/80 backdrop-blur-md transition-[width] ease-linear supports-[backdrop-filter]:bg-card/60 md:min-h-[64px]">
            {/* Left side - Navigation */}
            <div className="flex items-center gap-2 px-3 md:px-6">
                <SidebarTrigger className="-ml-1 transition-colors hover:bg-accent hover:text-accent-foreground" />
                <div className="hidden sm:block">
                    <Breadcrumbs breadcrumbs={breadcrumbs} />
                </div>
            </div>

            {/* Spacer */}
            <div className="flex-1"></div>

            {/* Right side - Actions (Fixed position) */}
            <div
                className="fixed right-3 flex items-center gap-2 md:right-6"
                style={{
                    top: 'calc(env(safe-area-inset-top, 0px) + 16px)',
                }}
            >
                {/* Global Search */}
                <GlobalSearch />

                {auth.user && <NotificationBell currentUserId={auth.user.id} />}

                <NavUser />
            </div>
        </header>
    );
}
