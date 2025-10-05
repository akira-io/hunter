import notificationController from '@/actions/App/Http/Controllers/Settings/NotificationController';
import HeadingSmall from '@/components/heading-small';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { useToast } from '@/hooks/use-toast';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { Bell, BellOff, CheckCircle, Mail, User, XCircle } from 'lucide-react';
import { useEffect, useRef } from 'react';

interface NotificationSettings {
    follow_notifications: boolean;
    email_notifications: boolean;
    browser_notifications: boolean;
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

export default function Notifications({ notificationSettings }: NotificationsProps) {
    const { toast } = useToast();
    const debounceTimerRef = useRef<NodeJS.Timeout | null>(null);

    const form = useForm<NotificationSettings>({
        follow_notifications: notificationSettings?.follow_notifications ?? true,
        email_notifications: notificationSettings?.email_notifications ?? true,
        browser_notifications: notificationSettings?.browser_notifications ?? true,
    });

    const { data, setData } = form;

    const handleToggleChange = (field: keyof NotificationSettings, value: boolean) => {
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
                        description: 'Suas configurações foram atualizadas automaticamente.',
                    });
                },
                onError: (errors) => {
                    console.error('Erro ao salvar notificações:', errors);
                    toast({
                        variant: 'destructive',
                        icon: <XCircle className="text-red-400" />,
                        title: 'Erro',
                        description: 'Erro ao atualizar as preferências. Tente novamente.',
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
                <div className="space-y-4 sm:space-y-6">
                    <HeadingSmall title="Notificações" description="Gerencie como você recebe notificações" />

                    <div className="space-y-4 sm:space-y-6">
                        {/* Follow Notifications (In-App) */}
                        <div className="gradient group relative flex items-start justify-between gap-4 rounded-lg border p-4 transition-all">
                            <div className="flex gap-3">
                                <div
                                    className={cn(
                                        'flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full transition-all duration-300',
                                        data.follow_notifications ? 'bg-purple-500/20' : 'bg-muted',
                                    )}
                                >
                                    <User
                                        className={cn(
                                            'h-5 w-5 transition-all duration-300',
                                            data.follow_notifications ? 'text-purple-500' : 'text-muted-foreground',
                                        )}
                                    />
                                </div>
                                <div className="flex-1 space-y-1">
                                    <Label htmlFor="follow_notifications" className="cursor-pointer text-base font-medium">
                                        Notificações de Seguidores (No App)
                                    </Label>
                                    <p className="text-muted-foreground text-sm">
                                        Receba notificações no aplicativo quando alguém começar a te seguir
                                    </p>
                                </div>
                            </div>
                            <Switch
                                id="follow_notifications"
                                checked={data.follow_notifications}
                                onCheckedChange={(checked) => handleToggleChange('follow_notifications', checked)}
                            />
                        </div>

                        {/* Browser Notifications */}
                        <div className="gradient group relative flex items-start justify-between gap-4 rounded-lg border p-4 transition-all">
                            <div className="flex gap-3">
                                <div
                                    className={cn(
                                        'flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full transition-all duration-300',
                                        data.browser_notifications ? 'bg-purple-500/20' : 'bg-muted',
                                    )}
                                >
                                    <Bell
                                        className={cn(
                                            'h-5 w-5 transition-all duration-300',
                                            data.browser_notifications ? 'text-purple-500' : 'text-muted-foreground',
                                        )}
                                    />
                                </div>
                                <div className="flex-1 space-y-1">
                                    <Label htmlFor="browser_notifications" className="cursor-pointer text-base font-medium">
                                        Notificações do Navegador
                                    </Label>
                                    <p className="text-muted-foreground text-sm">
                                        Receba notificações em tempo real no navegador para todas as atividades
                                    </p>
                                </div>
                            </div>
                            <Switch
                                id="browser_notifications"
                                checked={data.browser_notifications}
                                onCheckedChange={(checked) => handleToggleChange('browser_notifications', checked)}
                            />
                        </div>

                        {/* Email Notifications */}
                        <div className="gradient group relative flex items-start justify-between gap-4 rounded-lg border p-4 transition-all">
                            <div className="flex gap-3">
                                <div
                                    className={cn(
                                        'flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full transition-all duration-300',
                                        data.email_notifications ? 'bg-purple-500/20' : 'bg-muted',
                                    )}
                                >
                                    <Mail
                                        className={cn(
                                            'h-5 w-5 transition-all duration-300',
                                            data.email_notifications ? 'text-purple-500' : 'text-muted-foreground',
                                        )}
                                    />
                                </div>
                                <div className="flex-1 space-y-1">
                                    <Label htmlFor="email_notifications" className="cursor-pointer text-base font-medium">
                                        Notificações por Email
                                    </Label>
                                    <p className="text-muted-foreground text-sm">
                                        Receba resumos e atualizações importantes por email para todas as atividades
                                    </p>
                                </div>
                            </div>
                            <Switch
                                id="email_notifications"
                                checked={data.email_notifications}
                                onCheckedChange={(checked) => handleToggleChange('email_notifications', checked)}
                            />
                        </div>

                        {/* Info Box */}
                        <div className="gradient bg-muted/50 flex gap-3 rounded-lg border p-4">
                            <BellOff className="text-muted-foreground h-5 w-5 flex-shrink-0" />
                            <div className="text-muted-foreground space-y-1 text-sm">
                                <p className="font-medium">Como funcionam as notificações</p>
                                <p>
                                    Cada tipo de notificação funciona de forma independente. Por exemplo, você pode desativar notificações no app mas
                                    continuar a receber emails, ou vice-versa. As alterações são salvas automaticamente.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
