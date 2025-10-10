import NotificationController from '@/actions/App/Http/Controllers/Notification/NotificationController';
import { NotificationItem } from '@/components/notifications/NotificationItem';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import Layout from '@/layouts/app-layout';
import { useSetNotifications } from '@/stores/notificationStore';
import { Notification, NotificationCounts } from '@/types';
import { Head, InfiniteScroll, router } from '@inertiajs/react';
import { Bell, Check } from 'lucide-react';
import { useEffect } from 'react';

interface NotificationsPageProps {
    notifications: {
        data: Notification[];
    };
    unread_count: number;
    filter: string;
    counts: NotificationCounts;
}

export default function Notifications({
    notifications,
    unread_count,
    filter,
    counts,
}: NotificationsPageProps) {
    const setNotifications = useSetNotifications();

    // Sync Inertia data with store
    useEffect(() => {
        if (notifications?.data) {
            setNotifications(notifications.data);
        }
    }, [notifications?.data, setNotifications]);

    const handleNotificationClick = async (notification: Notification) => {
        // Marcar como lida se estiver não lida
        if (!notification.read_at) {
            router.post(
                NotificationController.read.url({ id: notification.id }),
                {},
                {
                    preserveScroll: true,
                    onError: (error) =>
                        console.error(
                            'Failed to mark notification as read:',
                            error,
                        ),
                },
            );
        }
    };

    const handleMarkAllAsRead = () => {
        router.post(
            NotificationController.readAll.url(),
            {},
            {
                preserveScroll: true,
                onError: (error) =>
                    console.error(
                        'Failed to mark all notifications as read:',
                        error,
                    ),
            },
        );
    };

    // Mostrar tab "Lidas" apenas se houver notificações não lidas
    const filters = [
        { key: 'all', label: 'Todas', count: counts.all },
        { key: 'unread', label: 'Não lidas', count: counts.unread },
        ...(counts.unread > 0
            ? [{ key: 'read', label: 'Lidas', count: counts.read }]
            : []),
    ];

    // Map filter key to tab value (1-indexed)
    const currentTabValue = filters.findIndex((f) => f.key === filter) + 1;

    const handleTabChange = (value: string) => {
        const tabIndex = parseInt(value.replace('tab-', '')) - 1;
        const selectedFilter = filters[tabIndex];
        if (selectedFilter) {
            router.get(
                NotificationController.index.url({
                    query: { filter: selectedFilter.key },
                }),
                {},
                { preserveScroll: true },
            );
        }
    };

    return (
        <Layout>
            <Head title="Notificações" />

            <div className="mx-auto max-w-4xl p-4 sm:p-6">
                {/* Header */}
                <div className="mb-8">
                    <div className="mb-6 flex items-start justify-between">
                        <div>
                            <h1 className="mb-2 text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                                Notificações
                            </h1>
                            {unread_count > 0 ? (
                                <p className="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                                    <span className="flex h-2 w-2">
                                        <span className="absolute inline-flex h-2 w-2 animate-ping rounded-full bg-purple-400 opacity-75"></span>
                                        <span className="relative inline-flex h-2 w-2 rounded-full bg-purple-500"></span>
                                    </span>
                                    {unread_count} nova
                                    {unread_count !== 1 ? 's' : ''} notificação
                                    {unread_count !== 1 ? 'ões' : ''}
                                </p>
                            ) : (
                                <p className="text-sm text-zinc-600 dark:text-zinc-400">
                                    Você está em dia com tudo! 🎉
                                </p>
                            )}
                        </div>
                        {unread_count > 0 && (
                            <Button
                                onClick={handleMarkAllAsRead}
                                variant="outline"
                                size="sm"
                                className="flex cursor-pointer items-center gap-2"
                            >
                                <Check size={16} />
                                Marcar todas como lidas
                            </Button>
                        )}
                    </div>
                </div>

                {/* Filter Tabs */}
                <Tabs
                    value={`tab-${currentTabValue}`}
                    onValueChange={handleTabChange}
                    className="mb-6"
                >
                    <TabsList className="gradient w-full sm:w-auto">
                        {filters.map(({ label, count }, index) => (
                            <TabsTrigger
                                key={`tab-${index + 1}`}
                                value={`tab-${index + 1}`}
                                className="cursor-pointer"
                            >
                                {label}
                                <span className="rounded-full bg-zinc-200 px-2 py-0.5 text-xs dark:bg-zinc-700">
                                    {count > 99 ? '99+' : count}
                                </span>
                            </TabsTrigger>
                        ))}
                    </TabsList>

                    {/* Tab Content - Same for all tabs since we're using server-side filtering */}
                    {filters.map((_, index) => (
                        <TabsContent
                            key={`tab-content-${index + 1}`}
                            value={`tab-${index + 1}`}
                            className="mt-6"
                        >
                            <div className="space-y-3">
                                {notifications.data.length === 0 ? (
                                    <div className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-zinc-300 bg-gradient-to-br from-zinc-50 to-zinc-100/50 p-12 text-center dark:border-zinc-700 dark:from-zinc-900/50 dark:to-zinc-800/30">
                                        <div className="mb-6 grid h-20 w-20 place-items-center rounded-2xl bg-gradient-to-br from-purple-100 to-purple-200 shadow-lg dark:from-purple-900/30 dark:to-purple-800/20">
                                            <Bell
                                                size={32}
                                                className="text-purple-600 dark:text-purple-400"
                                            />
                                        </div>
                                        <h3 className="mb-2 text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                                            {filter === 'unread'
                                                ? 'Nenhuma notificação não lida'
                                                : filter === 'read'
                                                  ? 'Nenhuma notificação lida'
                                                  : 'Nenhuma notificação'}
                                        </h3>
                                        <p className="max-w-md text-sm text-zinc-600 dark:text-zinc-400">
                                            {filter === 'unread'
                                                ? 'Você está em dia com todas as suas notificações! 🎉'
                                                : filter === 'read'
                                                  ? 'Você ainda não leu nenhuma notificação.'
                                                  : 'Quando você receber notificações, elas aparecerão aqui.'}
                                        </p>
                                    </div>
                                ) : (
                                    <InfiniteScroll data="notifications">
                                        {notifications.data.map(
                                            (notification) => (
                                                <NotificationItem
                                                    key={notification.id}
                                                    notification={notification}
                                                    onClick={
                                                        handleNotificationClick
                                                    }
                                                    hideUnreadDot={
                                                        filter === 'read'
                                                    }
                                                />
                                            ),
                                        )}
                                    </InfiniteScroll>
                                )}
                            </div>
                        </TabsContent>
                    ))}
                </Tabs>
            </div>
        </Layout>
    );
}
