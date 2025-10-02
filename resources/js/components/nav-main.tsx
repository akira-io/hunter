import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem
} from '@/components/ui/sidebar';
import { cn } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';

export function NavMain({ items = [] }: { items: NavItem[] }) {
    const page = usePage();
    
    // Helper to check if route is active - using window.location.pathname like settings layout
    const isRouteActive = (href: string) => {
        if (typeof window === 'undefined') return false;
        
        const currentPath = window.location.pathname.replace(/\/$/, ''); // Remove trailing slash
        const itemPath = href.split('?')[0].replace(/\/$/, ''); // Remove trailing slash and query params
        
        // Exact match or starts with the path followed by /
        return currentPath === itemPath || currentPath.startsWith(itemPath + '/');
    };
    
    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel className="text-muted-foreground/70 text-xs font-semibold tracking-wider uppercase">Menu</SidebarGroupLabel>
            <SidebarMenu className="gap-1">
                {items.map((item) => {
                    const isActive = isRouteActive(item.href);
                    return (
                        <SidebarMenuItem key={item.title}>
                            <SidebarMenuButton
                                asChild
                                isActive={isActive}
                                tooltip={{ children: item.title }}
                                className={cn(
                                    'group hover:bg-accent/50 transition-all duration-200',
                                    isActive && 'bg-accent text-accent-foreground font-medium shadow-sm',
                                )}
                            >
                                <Link href={item.href} prefetch className="flex items-center gap-3">
                                    {item.icon && (
                                        <item.icon
                                            className={cn(
                                                'h-5 w-5 transition-transform duration-200 group-hover:scale-110',
                                                isActive && 'text-primary',
                                            )}
                                        />
                                    )}
                                    <span className="truncate">{item.title}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    );
                })}
            </SidebarMenu>
        </SidebarGroup>
    );
}
