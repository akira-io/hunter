import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem
} from '@/components/ui/sidebar';
import { useChatContext } from '@/contexts/ChatContext';
import finder from '@/routes/finder';
import followable from '@/routes/followable';
import hunts from '@/routes/hunts';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { BookOpen, EyeIcon, FileSearch, MessageCircle, MessageCircleMore, NetworkIcon, Sparkles } from 'lucide-react';
import { useMemo } from 'react';
import { AiFillGithub } from 'react-icons/ai';

const mainNavItems: NavItem[] = [
    {
        title: 'Hunts',
        href: hunts.index.url(),
        icon: Sparkles,
    },
    {
        title: 'Explorar',
        href: finder.index.url(),
        icon: FileSearch,
    },
    {
        title: 'Hunters',
        href: followable.followers.url(),
        icon: EyeIcon,
    },
    {
        title: 'Huntings',
        href: followable.followings.url(),
        icon: NetworkIcon,
    },
    {
        title: 'Chat',
        href: '/chat',
        icon: MessageCircle,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Comunidade',
        href: 'https://discord.gg/ghPqZg3RcZ',
        icon: MessageCircleMore,
    },
    {
        title: 'Repositório',
        href: 'https://github.com/hunter-cv/web',
        icon: AiFillGithub,
    },
    {
        title: 'Documentação',
        href: 'https://github.com/hunter-cv/web/blob/develop/docs/01-getting-started.md',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    const { conversations } = useChatContext();

    // Calculate total unread messages
    const totalUnreadCount = useMemo(() => {
        return conversations.reduce((total, conv) => {
            return total + (conv.unread_count || 0);
        }, 0);
    }, [conversations]);

    // Add badge count to chat item
    const itemsWithBadge = useMemo(() => {
        return mainNavItems.map((item) => {
            if (item.title === 'Chat' && totalUnreadCount > 0) {
                return {
                    ...item,
                    badge: totalUnreadCount,
                };
            }
            return item;
        });
    }, [totalUnreadCount]);

    return (
        <Sidebar collapsible="icon" variant="sidebar" className="border-border/50 border-r">
            <SidebarHeader className="border-border/50 border-b">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild className="group hover:bg-accent/50 transition-all duration-200">
                            <Link href="/" prefetch className="flex items-center gap-2">
                                <AppLogo className="transition-transform duration-200 group-hover:scale-105" />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>
            <SidebarContent className="gap-0 py-4">
                <NavMain items={itemsWithBadge} />
            </SidebarContent>
            <SidebarFooter className="mt-auto border-t-0 pb-4">
                <NavFooter items={footerNavItems} />
            </SidebarFooter>
        </Sidebar>
    );
}
