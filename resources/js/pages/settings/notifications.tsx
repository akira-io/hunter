import notificationController from '@/actions/App/Http/Controllers/Settings/NotificationController';
import HeadingSmall from '@/components/heading-small';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { useToast } from '@/hooks/use-toast';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import {
    Bell,
    BellOff,
    CheckCircle,
    Mail,
    Megaphone,
    User,
    XCircle,
} from 'lucide-react';
import { useEffect, useRef } from 'react';

interface NotificationSettings {
    follow_notifications: boolean;
    email_notifications: boolean;
    browser_notifications: boolean;
    hunt_notifications_in_app: boolean;
    hunt_notifications_browser: boolean;
    hunt_notifications_email: boolean;
}

interface NotificationsProps {
    notificationSettings: NotificationSettings;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Notificações',
        href: '/settings/notifications',
    },
];

export default function Notifications({
    notificationSettings,
}: NotificationsProps) {
    const { toast } = useToast();
    const debounceTimerRef = useRef<NodeJS.Timeout | null>(null);

    const form = useForm<NotificationSettings>({
        follow_notifications:
            notificationSettings?.follow_notifications ?? true,
        email_notifications: notificationSettings?.email_notifications ?? true,
        browser_notifications:
            notificationSettings?.browser_notifications ?? true,
        hunt_notifications_in_app:
            notificationSettings?.hunt_notifications_in_app ?? true,
        hunt_notifications_browser:
            notificationSettings?.hunt_notifications_browser ?? true,
        hunt_notifications_email:
            notificationSettings?.hunt_notifications_email ?? false,
    });

    const { data, setData } = form;

    const handleToggleChange = (
        field: keyof NotificationSettings,
        value: boolean,
    ) => {
        // Create updated data object with the new value
        const updatedData = { ...data, [field]: value };

        setData(updatedData);

        if (debounceTimerRef.current) {
            clearTimeout(debounceTimerRef.current);
        }

        debounceTimerRef.current = setTimeout(() => {
            form.transform(() => updatedData);

            form.patch(notificationController.update().url, {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    toast({
                        icon: <CheckCircle className="text-green-400" />,
                        title: 'Preferências salvas',
                        description:
                            'Suas configurações foram atualizadas automaticamente.',
                    });
                },
                onError: (errors) => {
                    console.error('Erro ao salvar notificações:', errors);
                    toast({
                        variant: 'destructive',
                        icon: <XCircle className="text-red-400" />,
                        title: 'Erro',
                        description:
                            'Erro ao atualizar as preferências. Tente novamente.',
                    });
                },
            });
        }, 300);
    };

    // Cleanup on unmount
    useEffect(() => {
        return () => {
            if (debounceTimerRef.current) {
                clearTimeout(debounceTimerRef.current);
            }
        };
    }, []);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Notificações - Definições" />
            <SettingsLayout>
                <div className="space-y-4">
                    <HeadingSmall
                        title="Notificações"
                        description="Gerencie como você recebe notificações"
                    />

                    {/* Info Box */}
                    <div className="gradient flex gap-3 rounded-lg border bg-muted/50 p-4">
                        <BellOff className="h-5 w-5 flex-shrink-0 text-muted-foreground" />
                        <div className="space-y-1 text-sm text-muted-foreground">
                            <p className="font-medium">
                                Como funcionam as notificações
                            </p>
                            <p>
                                Cada tipo de notificação funciona de forma
                                independente. Por exemplo, você pode desativar
                                notificações no app mas continuar a receber
                                emails, ou vice-versa. As alterações são salvas
                                automaticamente.
                            </p>
                        </div>
                    </div>
                    <Card>
                        <CardHeader>
                            <CardTitle>Geral</CardTitle>
                            <CardDescription>
                                Gerencie suas preferências de notificações
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {/* Follow Notifications (In-App) */}
                            <div className="gradient group relative flex items-start justify-between gap-4 rounded-lg border p-4 transition-all">
                                <div className="flex gap-3">
                                    <div
                                        className={cn(
                                            'flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full transition-all duration-300',
                                            data.follow_notifications
                                                ? 'bg-purple-500/20'
                                                : 'bg-muted',
                                        )}
                                    >
                                        <User
                                            className={cn(
                                                'h-5 w-5 transition-all duration-300',
                                                data.follow_notifications
                                                    ? 'text-purple-500'
                                                    : 'text-muted-foreground',
                                            )}
                                        />
                                    </div>
                                    <div className="flex-1 space-y-1">
                                        <Label
                                            htmlFor="follow_notifications"
                                            className="cursor-pointer text-base font-medium"
                                        >
                                            Notificações de Seguidores (No App)
                                        </Label>
                                        <p className="text-sm text-muted-foreground">
                                            Receba notificações no aplicativo
                                            quando alguém começar a te seguir
                                        </p>
                                    </div>
                                </div>
                                <Switch
                                    id="follow_notifications"
                                    checked={data.follow_notifications}
                                    onCheckedChange={(checked) =>
                                        handleToggleChange(
                                            'follow_notifications',
                                            checked,
                                        )
                                    }
                                />
                            </div>

                            {/* Browser Notifications */}
                            <div className="gradient group relative flex items-start justify-between gap-4 rounded-lg border p-4 transition-all">
                                <div className="flex gap-3">
                                    <div
                                        className={cn(
                                            'flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full transition-all duration-300',
                                            data.browser_notifications
                                                ? 'bg-purple-500/20'
                                                : 'bg-muted',
                                        )}
                                    >
                                        <Bell
                                            className={cn(
                                                'h-5 w-5 transition-all duration-300',
                                                data.browser_notifications
                                                    ? 'text-purple-500'
                                                    : 'text-muted-foreground',
                                            )}
                                        />
                                    </div>
                                    <div className="flex-1 space-y-1">
                                        <Label
                                            htmlFor="browser_notifications"
                                            className="cursor-pointer text-base font-medium"
                                        >
                                            Notificações em tempo real
                                        </Label>
                                        <p className="text-sm text-muted-foreground">
                                            Receba notificações em tempo real
                                            para todas as atividades
                                        </p>
                                    </div>
                                </div>
                                <Switch
                                    id="browser_notifications"
                                    checked={data.browser_notifications}
                                    onCheckedChange={(checked) =>
                                        handleToggleChange(
                                            'browser_notifications',
                                            checked,
                                        )
                                    }
                                />
                            </div>

                            {/* Email Notifications */}
                            <div className="gradient group relative flex items-start justify-between gap-4 rounded-lg border p-4 transition-all">
                                <div className="flex gap-3">
                                    <div
                                        className={cn(
                                            'flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full transition-all duration-300',
                                            data.email_notifications
                                                ? 'bg-purple-500/20'
                                                : 'bg-muted',
                                        )}
                                    >
                                        <Mail
                                            className={cn(
                                                'h-5 w-5 transition-all duration-300',
                                                data.email_notifications
                                                    ? 'text-purple-500'
                                                    : 'text-muted-foreground',
                                            )}
                                        />
                                    </div>
                                    <div className="flex-1 space-y-1">
                                        <Label
                                            htmlFor="email_notifications"
                                            className="cursor-pointer text-base font-medium"
                                        >
                                            Notificações por Email
                                        </Label>
                                        <p className="text-sm text-muted-foreground">
                                            Receba resumos e atualizações
                                            importantes por email para todas as
                                            atividades
                                        </p>
                                    </div>
                                </div>
                                <Switch
                                    id="email_notifications"
                                    checked={data.email_notifications}
                                    onCheckedChange={(checked) =>
                                        handleToggleChange(
                                            'email_notifications',
                                            checked,
                                        )
                                    }
                                />
                            </div>

                            {/* Divider */}
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle>Hunts</CardTitle>
                            <CardDescription>
                                Notificações relacionadas a novos hunts
                            </CardDescription>
                        </CardHeader>

                        <CardContent className="space-y-4">
                            {/* Hunt Notifications - In App */}
                            <div className="gradient group relative flex items-start justify-between gap-4 rounded-lg border p-4 transition-all">
                                <div className="flex gap-3">
                                    <div
                                        className={cn(
                                            'flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full transition-all duration-300',
                                            data.hunt_notifications_in_app
                                                ? 'bg-purple-500/20'
                                                : 'bg-muted',
                                        )}
                                    >
                                        <Megaphone
                                            className={cn(
                                                'h-5 w-5 transition-all duration-300',
                                                data.hunt_notifications_in_app
                                                    ? 'text-purple-500'
                                                    : 'text-muted-foreground',
                                            )}
                                        />
                                    </div>
                                    <div className="flex-1 space-y-1">
                                        <Label
                                            htmlFor="hunt_notifications_in_app"
                                            className="cursor-pointer text-base font-medium"
                                        >
                                            Novos Hunts (No App)
                                        </Label>
                                        <p className="text-sm text-muted-foreground">
                                            Receba notificações no app quando
                                            hunters que você segue publicarem
                                            novos hunts
                                        </p>
                                    </div>
                                </div>
                                <Switch
                                    id="hunt_notifications_in_app"
                                    checked={data.hunt_notifications_in_app}
                                    onCheckedChange={(checked) =>
                                        handleToggleChange(
                                            'hunt_notifications_in_app',
                                            checked,
                                        )
                                    }
                                />
                            </div>

                            {/* Hunt Notifications - Browser */}
                            <div className="gradient group relative flex items-start justify-between gap-4 rounded-lg border p-4 transition-all">
                                <div className="flex gap-3">
                                    <div
                                        className={cn(
                                            'flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full transition-all duration-300',
                                            data.hunt_notifications_browser
                                                ? 'bg-purple-500/20'
                                                : 'bg-muted',
                                        )}
                                    >
                                        <Bell
                                            className={cn(
                                                'h-5 w-5 transition-all duration-300',
                                                data.hunt_notifications_browser
                                                    ? 'text-purple-500'
                                                    : 'text-muted-foreground',
                                            )}
                                        />
                                    </div>
                                    <div className="flex-1 space-y-1">
                                        <Label
                                            htmlFor="hunt_notifications_browser"
                                            className="cursor-pointer text-base font-medium"
                                        >
                                            Novos Hunts (Tempo Real)
                                        </Label>
                                        <p className="text-sm text-muted-foreground">
                                            Receba notificações em tempo real
                                            quando novos hunts forem publicados
                                        </p>
                                    </div>
                                </div>
                                <Switch
                                    id="hunt_notifications_browser"
                                    checked={data.hunt_notifications_browser}
                                    onCheckedChange={(checked) =>
                                        handleToggleChange(
                                            'hunt_notifications_browser',
                                            checked,
                                        )
                                    }
                                />
                            </div>

                            {/* Hunt Notifications - Email */}
                            <div className="gradient group relative flex items-start justify-between gap-4 rounded-lg border p-4 transition-all">
                                <div className="flex gap-3">
                                    <div
                                        className={cn(
                                            'flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full transition-all duration-300',
                                            data.hunt_notifications_email
                                                ? 'bg-purple-500/20'
                                                : 'bg-muted',
                                        )}
                                    >
                                        <Mail
                                            className={cn(
                                                'h-5 w-5 transition-all duration-300',
                                                data.hunt_notifications_email
                                                    ? 'text-purple-500'
                                                    : 'text-muted-foreground',
                                            )}
                                        />
                                    </div>
                                    <div className="flex-1 space-y-1">
                                        <Label
                                            htmlFor="hunt_notifications_email"
                                            className="cursor-pointer text-base font-medium"
                                        >
                                            Novos Hunts (Email)
                                        </Label>
                                        <p className="text-sm text-muted-foreground">
                                            Receba emails quando hunters que
                                            você segue publicarem novos hunts
                                            (recomendado: desativado)
                                        </p>
                                    </div>
                                </div>
                                <Switch
                                    id="hunt_notifications_email"
                                    checked={data.hunt_notifications_email}
                                    onCheckedChange={(checked) =>
                                        handleToggleChange(
                                            'hunt_notifications_email',
                                            checked,
                                        )
                                    }
                                />
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
