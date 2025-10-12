import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Bell,
    Lock,
    Palette,
    Settings2,
    Shield,
} from 'lucide-react';
import { type PropsWithChildren } from 'react';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Geral',
        href: '/settings/general',
        icon: Settings2,
    },
    {
        title: 'Segurança',
        href: '/settings/security',
        icon: Shield,
    },
    {
        title: 'Privacidade',
        href: '/settings/privacy',
        icon: Lock,
    },
    {
        title: 'Notificações',
        href: '/settings/notifications',
        icon: Bell,
    },
    {
        title: 'Aparência',
        href: '/settings/appearance',
        icon: Palette,
    },
];

export default function SettingsLayout({ children }: PropsWithChildren) {
    // When server-side rendering, we only render the layout on the client...
    if (typeof window === 'undefined') {
        return null;
    }

    const currentPath = window.location.pathname;

    return (
        <div className="container mx-auto max-w-7xl items-center px-4 py-6 md:px-0 lg:max-w-5xl">
            {/* Mobile: Back button + Title */}
            <div className="mb-6 lg:hidden">
                <Link
                    href="/settings"
                    className="mb-4 inline-flex items-center gap-2 text-sm text-zinc-600 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                >
                    <ArrowLeft className="size-4" />
                    Voltar
                </Link>
                <Heading
                    title="Definições"
                    description="Gerencie as configurações da sua conta"
                />
            </div>

            {/* Desktop: Title */}
            <div className="mb-8 hidden lg:block">
                <Heading
                    title="Definições"
                    description="Gerencie as configurações da sua conta"
                />
            </div>

            <div className="flex flex-col gap-8 lg:flex-row lg:gap-12">
                {/* Desktop Sidebar */}
                <aside className="hidden lg:block lg:w-64">
                    <Card className="gradient sticky top-24 p-2">
                        <CardContent className="p-0">
                            <nav className="space-y-1">
                                {sidebarNavItems.map((item, index) => {
                                    const Icon = item.icon;
                                    return (
                                        <Button
                                            key={`${item.href}-${index}`}
                                            size="sm"
                                            variant="ghost"
                                            asChild
                                            className={cn(
                                                'w-full justify-start',
                                                {
                                                    'bg-white/10 font-medium':
                                                        currentPath ===
                                                        item.href,
                                                },
                                            )}
                                        >
                                            <Link href={item.href} prefetch>
                                                {Icon && (
                                                    <Icon className="mr-2 size-4" />
                                                )}
                                                {item.title}
                                            </Link>
                                        </Button>
                                    );
                                })}
                            </nav>
                        </CardContent>
                    </Card>
                </aside>

                {/* Content */}
                <div className="flex-1 overflow-auto">
                    <Card className="p-2">
                        <CardContent className="p-0">{children}</CardContent>
                    </Card>
                </div>
            </div>
        </div>
    );
}
