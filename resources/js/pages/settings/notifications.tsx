import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { useToast } from '@/hooks/use-toast';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { Bell, BellOff, CheckCircle, Mail, User, XCircle } from 'lucide-react';

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
    
    const { data, setData, patch, processing } = useForm<NotificationSettings>({
        follow_notifications: notificationSettings?.follow_notifications ?? true,
        email_notifications: notificationSettings?.email_notifications ?? true,
        browser_notifications: notificationSettings?.browser_notifications ?? true,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        patch('/settings/notifications', {
            preserveScroll: true,
            onSuccess: () => {
                toast({
                    icon: <CheckCircle className="text-green-400" />,
                    title: 'Sucesso',
                    description: 'Preferências de notificação atualizadas com sucesso.',
                });
            },
            onError: () => {
                toast({
                    variant: 'destructive',
                    icon: <XCircle className="text-red-400" />,
                    title: 'Erro',
                    description: 'Erro ao atualizar as preferências. Tente novamente.',
                });
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Notificações - Definições" />
            <SettingsLayout>
                <div className="space-y-6">
                    <div>
                        <HeadingSmall title="Notificações" description="Gerencie como você recebe notificações" />
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* Browser Notifications */}
                        <div className="flex items-start justify-between gap-4 rounded-lg border p-4">
                            <div className="flex gap-3">
                                <div className="bg-primary/10 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full">
                                    <Bell className="text-primary h-5 w-5" />
                                </div>
                                <div className="flex-1 space-y-1">
                                    <Label htmlFor="browser_notifications" className="cursor-pointer text-base font-medium">
                                        Notificações do Navegador
                                    </Label>
                                    <p className="text-muted-foreground text-sm">Receba notificações em tempo real no navegador</p>
                                </div>
                            </div>
                            <Switch
                                id="browser_notifications"
                                checked={data.browser_notifications}
                                onCheckedChange={(checked) => setData('browser_notifications', checked)}
                            />
                        </div>

                        {/* Email Notifications */}
                        <div className="flex items-start justify-between gap-4 rounded-lg border p-4">
                            <div className="flex gap-3">
                                <div className="bg-primary/10 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full">
                                    <Mail className="text-primary h-5 w-5" />
                                </div>
                                <div className="flex-1 space-y-1">
                                    <Label htmlFor="email_notifications" className="cursor-pointer text-base font-medium">
                                        Notificações por Email
                                    </Label>
                                    <p className="text-muted-foreground text-sm">Receba resumos e atualizações importantes por email</p>
                                </div>
                            </div>
                            <Switch
                                id="email_notifications"
                                checked={data.email_notifications}
                                onCheckedChange={(checked) => setData('email_notifications', checked)}
                            />
                        </div>

                        {/* Follow Notifications */}
                        <div className="flex items-start justify-between gap-4 rounded-lg border p-4">
                            <div className="flex gap-3">
                                <div className="bg-primary/10 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full">
                                    <User className="text-primary h-5 w-5" />
                                </div>
                                <div className="flex-1 space-y-1">
                                    <Label htmlFor="follow_notifications" className="cursor-pointer text-base font-medium">
                                        Notificações de Seguidores
                                    </Label>
                                    <p className="text-muted-foreground text-sm">Seja notificado quando alguém começar a te seguir</p>
                                </div>
                            </div>
                            <Switch
                                id="follow_notifications"
                                checked={data.follow_notifications}
                                onCheckedChange={(checked) => setData('follow_notifications', checked)}
                            />
                        </div>

                        {/* Info Box */}
                        <div className="bg-muted/50 flex gap-3 rounded-lg border p-4">
                            <BellOff className="text-muted-foreground h-5 w-5 flex-shrink-0" />
                            <div className="text-muted-foreground space-y-1 text-sm">
                                <p className="font-medium">Sobre as notificações</p>
                                <p>
                                    Você pode desativar tipos específicos de notificações a qualquer momento. As alterações são aplicadas
                                    imediatamente após salvar.
                                </p>
                            </div>
                        </div>

                        {/* Submit Button */}
                        <div className="flex justify-end">
                            <Button type="submit" disabled={processing} variant="gradient">
                                {processing ? 'Salvando...' : 'Salvar Preferências'}
                            </Button>
                        </div>
                    </form>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
