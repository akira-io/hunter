import Heading from '@/components/heading';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { appearance } from '@/routes';
import { notifications } from '@/routes/settings';
import { Link } from '@inertiajs/react';
import {
    Bell,
    ChevronRight,
    Lock,
    Palette,
    Settings2,
    Shield,
} from 'lucide-react';

interface SettingItem {
    title: string;
    description: string;
    href: string;
    icon: React.ReactNode;
}

const settingsItems: SettingItem[] = [
    {
        title: 'Geral',
        description: 'Gerencie configurações gerais da conta',
        href: '/settings/general',
        icon: <Settings2 className="size-5" />,
    },
    {
        title: 'Segurança',
        description: 'Gerencie suas configurações de segurança',
        href: '/settings/security',
        icon: <Shield className="size-5" />,
    },
    {
        title: 'Privacidade',
        description: 'Controle quem pode ver o seu perfil e interagir consigo',
        href: '/settings/privacy',
        icon: <Lock className="size-5" />,
    },
    {
        title: 'Notificações',
        description: 'Gerencie suas preferências de notificação',
        href: notifications().url,
        icon: <Bell className="size-5" />,
    },
    {
        title: 'Aparência',
        description: 'Personalize o tema da aplicação',
        href: appearance().url,
        icon: <Palette className="size-5" />,
    },
];

export default function SettingsIndex() {
    return (
        <AppLayout>
            <div className="container mx-auto max-w-4xl px-4 py-6">
                <Heading
                    title="Definições"
                    description="Gerencie as configurações da sua conta"
                />

                <div className="grid space-y-2 md:grid-cols-2 md:items-center md:justify-center md:gap-2 md:space-y-0">
                    {settingsItems.map((item) => (
                        <Link key={item.href} href={item.href}>
                            <Card className="gradient group cursor-pointer transition-all hover:shadow-lg active:scale-[0.99]">
                                <CardContent className="flex h-16 items-center gap-4 p-4">
                                    <div className="flex size-12 shrink-0 items-center justify-center rounded-full bg-white/10 transition-colors group-hover:bg-white/20">
                                        {item.icon}
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <h3 className="font-semibold">
                                            {item.title}
                                        </h3>
                                        <p className="text-sm opacity-80">
                                            {item.description}
                                        </p>
                                    </div>
                                    <ChevronRight className="size-5 shrink-0 opacity-50 transition-transform group-hover:translate-x-1 group-hover:opacity-100" />
                                </CardContent>
                            </Card>
                        </Link>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
