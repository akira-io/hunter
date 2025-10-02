import { Icon } from '@/components/icon';
import { SidebarGroup, SidebarGroupContent, SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from '@/components/ui/sidebar';
import { cn } from '@/lib/utils';
import { type NavItem } from '@/types';
import { ExternalLink } from 'lucide-react';
import { type ComponentPropsWithoutRef } from 'react';

export function NavFooter({
    items,
    className,
    ...props
}: ComponentPropsWithoutRef<typeof SidebarGroup> & {
    items: NavItem[];
}) {
    const { isMobile, setOpenMobile } = useSidebar();

    const handleLinkClick = () => {
        // Fecha o sidebar apenas no mobile
        if (isMobile) {
            setOpenMobile(false);
        }
    };

    return (
        <SidebarGroup {...props} className={cn('border-border/50 border-t pt-2 group-data-[collapsible=icon]:p-0', className)}>
            <SidebarGroupContent>
                <SidebarMenu className="gap-2">
                    {items.map((item) => (
                        <SidebarMenuItem key={item.title}>
                            <SidebarMenuButton
                                asChild
                                className="group text-muted-foreground hover:bg-accent/50 hover:text-foreground h-11 transition-all duration-200"
                            >
                                <a
                                    href={item.href}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="flex items-center justify-between gap-2 py-2.5"
                                    onClick={handleLinkClick}
                                >
                                    <div className="flex items-center gap-3">
                                        {item.icon && (
                                            <Icon
                                                iconNode={item.icon}
                                                className="h-[18px] w-[18px] transition-transform duration-200 group-hover:scale-110"
                                            />
                                        )}
                                        <span className="truncate text-[15px]">{item.title}</span>
                                    </div>
                                    <ExternalLink className="h-3.5 w-3.5 opacity-0 transition-opacity duration-200 group-hover:opacity-50" />
                                </a>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    ))}
                </SidebarMenu>
            </SidebarGroupContent>
        </SidebarGroup>
    );
}
