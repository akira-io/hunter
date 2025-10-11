import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { cn } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';

export function NavMain({ items = [] }: { items: NavItem[] }) {
    usePage();
    const { isMobile, setOpenMobile } = useSidebar();

    // Helper to check if route is active - using window.location.pathname like settings layout
    const isRouteActive = (href: string) => {
        if (typeof window === 'undefined') return false;

        const currentPath = window.location.pathname.replace(/\/$/, ''); // Remove trailing slash
        const itemPath = href.split('?')[0].replace(/\/$/, ''); // Remove trailing slash and query params

        // Exact match or starts with the path followed by /
        return (
            currentPath === itemPath || currentPath.startsWith(itemPath + '/')
        );
    };

    const handleLinkClick = () => {
        if (isMobile) {
            setOpenMobile(false);
        }
    };

    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel className="text-xs font-semibold tracking-wider text-muted-foreground/70 uppercase">
                Menu
            </SidebarGroupLabel>
            <SidebarMenu className="gap-2">
                {items.map((item) => {
                    const isActive = isRouteActive(item.href);
                    return (
                        <SidebarMenuItem key={item.title}>
                            <SidebarMenuButton
                                asChild
                                isActive={isActive}
                                tooltip={{ children: item.title }}
                                className={cn(
                                    'group h-11 transition-all duration-200 hover:bg-accent/50',
                                    isActive &&
                                        'bg-accent font-medium text-accent-foreground shadow-sm',
                                )}
                            >
                                <Link
                                    href={item.href}
                                    prefetch
                                    className="flex items-center gap-3 py-2.5"
                                    onClick={handleLinkClick}
                                >
                                    <div className="relative">
                                        {item.icon && (
                                            <item.icon
                                                className={cn(
                                                    'h-5 w-5 transition-transform duration-200 group-hover:scale-110',
                                                    isActive && 'text-primary',
                                                )}
                                            />
                                        )}
                                        {item.badge && item.badge > 0 && (
                                            <div className="absolute -top-1.5 -right-1.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-purple-500 px-1 text-[10px] font-bold text-white">
                                                {item.badge > 99
                                                    ? '99+'
                                                    : item.badge}
                                            </div>
                                        )}
                                    </div>
                                    <span className="truncate text-[15px]">
                                        {item.title}
                                    </span>
                                    {item.badge && item.badge > 0 && (
                                        <div className="ml-auto flex h-5 min-w-[20px] items-center justify-center rounded-full bg-purple-500 px-1.5 text-xs font-bold text-white">
                                            {item.badge > 99
                                                ? '99+'
                                                : item.badge}
                                        </div>
                                    )}
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    );
                })}
            </SidebarMenu>
        </SidebarGroup>
    );
}
