import { Breadcrumbs } from '@/components/breadcrumbs';
import { GlobalSearch } from '@/components/GlobalSearch';
import { NavUser } from '@/components/nav-user';
import { NotificationBell } from '@/components/notifications/NotificationBell';
import { SidebarTrigger, useSidebar } from '@/components/ui/sidebar';
import { type BreadcrumbItem as BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/react';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    const { auth } = usePage<{ auth: { user?: { id: number } } }>().props;
    const { state, isMobile } = useSidebar();

    // Calcula o offset baseado no estado do sidebar (apenas em desktop quando expandido)
    const sidebarOffset =
        !isMobile && state === 'expanded'
            ? 'calc(var(--sidebar-width) + 0.75rem)'
            : undefined;

    return (
        <header className="gradient sticky top-0 z-50 flex h-16 w-full shrink-0 items-center border-b border-sidebar-border/50 bg-card/80 backdrop-blur-md transition-[width] ease-linear supports-[backdrop-filter]:bg-card/60">
            {/* Left side - Navigation */}
            <div className="flex items-center gap-2 px-3 md:px-6">
                <SidebarTrigger className="-ml-1 transition-colors hover:bg-accent hover:text-accent-foreground" />
                <div className="hidden sm:block">
                    <Breadcrumbs breadcrumbs={breadcrumbs} />
                </div>
            </div>

            {/* Spacer */}
            <div className="flex-1"></div>

            {/* Right side - Actions (Fixed position to avoid sidebar push) */}
            <div
                className="fixed top-0 right-3 flex h-16 items-center gap-2 transition-[right] duration-200 ease-linear md:right-15"
                style={sidebarOffset ? { right: sidebarOffset } : undefined}
            >
                {/* Global Search */}
                <GlobalSearch />

                {auth.user && <NotificationBell currentUserId={auth.user.id} />}

                <NavUser />
            </div>
        </header>
    );
}
