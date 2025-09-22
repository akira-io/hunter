import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { BookOpen, EyeIcon, FileSearch, MessageCircleMore, NetworkIcon, RssIcon } from 'lucide-react';
import { AiFillGithub } from 'react-icons/ai';
import hunts from '@/routes/hunts';
import finder from '@/routes/finder';
import followable from '@/routes/followable';

const mainNavItems: NavItem[] = [
    {
        title: 'Hunt Line',
        href: hunts.index.url(),
        icon: RssIcon,
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
];

const footerNavItems: NavItem[] = [
    {
        title: 'Comunidade',
        href: 'https://discord.gg/ghPqZg3RcZ',
        icon: MessageCircleMore,
    },
    {
        title: 'Repositório',
        href: 'https://github.com/akira-io/hunter',
        icon: AiFillGithub,
    },
    {
        title: 'Documentação',
        href: 'https://github.com/akira-io/hunter/blob/main/README.md',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="sidebar">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>
            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>
            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
            </SidebarFooter>
        </Sidebar>
    );
}
