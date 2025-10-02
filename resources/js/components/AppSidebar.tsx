import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import finder from '@/routes/finder';
import followable from '@/routes/followable';
import hunts from '@/routes/hunts';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { BookOpen, EyeIcon, FileSearch, MessageCircleMore, NetworkIcon, RssIcon } from 'lucide-react';
import { AiFillGithub } from 'react-icons/ai';

const mainNavItems: NavItem[] = [
    {
        title: 'Hunts',
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
                <NavMain items={mainNavItems} />
            </SidebarContent>
            <SidebarFooter className="mt-auto border-t-0 pb-4">
                <NavFooter items={footerNavItems} />
            </SidebarFooter>
        </Sidebar>
    );
}
