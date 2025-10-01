import { SectionHeader } from '@/components/feed/SectionHeader';
import { NotificationItem } from '@/components/notifications/NotificationItem';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Pagination } from '@/components/ui/pagination';
import Layout from '@/layouts/app-layout';
import { Notification, NotificationCounts, PaginationInfo, SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Bell, Check } from 'lucide-react';
import React from 'react';
import NotificationController from '@/actions/App/Http/Controllers/Notification/NotificationController';
import { cn } from '@/lib/utils';

interface NotificationsPageProps {
    notifications: Notification[];
    pagination: PaginationInfo;
    unread_count: number;
    filter: string;
    counts: NotificationCounts;
}

export default function Notifications({
                                               notifications,
                                               pagination,
                                               unread_count,
                                               filter,
                                               counts,
                                           }: NotificationsPageProps) {
    const { auth } = usePage<SharedData>().props;

    const handleNotificationClick = async (notification: Notification) => {
        // Marcar como lida se estiver não lida
        if (!notification.read_at) {
            router.post(NotificationController.read.url({ id: notification.id }), {}, {
                preserveScroll: true,
                onError: (error) => console.error('Failed to mark notification as read:', error),
            });
        }
    };

    const handleMarkAllAsRead = () => {
        router.post(NotificationController.readAll.url(), {}, {
            preserveScroll: true,
            onError: (error) => console.error('Failed to mark all notifications as read:', error),
        });
    };

    const tabBase =
        'inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors';

    const tabVariants = {
        active: 'bg-purple-100 text-purple-700 dark:bg-purple-900/20 dark:text-purple-300',
        inactive: 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800',
    };

    const filters = [
        { key: 'all', label: 'Todas', count: counts.all },
        { key: 'unread', label: 'Não lidas', count: counts.unread },
        { key: 'read', label: 'Lidas', count: counts.read },
    ];

    return (
        <Layout>
            <Head title="Notificações" />

            <div className="mx-auto max-w-4xl p-4 sm:p-6">
                {/* Header */}
                <div className="mb-6 flex items-start justify-between">
                    <SectionHeader
                        title="Notificações"
                        description={`Você tem ${unread_count} notificação${
                            unread_count !== 1 ? 's' : ''
                        } não lida${unread_count !== 1 ? 's' : ''}`}
                    />
                    {unread_count > 0 && (
                        <Button
                            onClick={handleMarkAllAsRead}
                            variant="outline"
                            size="sm"
                            className="flex items-center gap-2 cursor-pointer"
                        >
                            <Check size={16} />
                            Marcar todas como lidas
                        </Button>
                    )}
                </div>

                {/* Filter Tabs */}
                <div className="mb-6">
                    <div className="flex flex-wrap gap-2">
                        {filters.map(({ key, label, count }) => (
                            <Link
                                key={key}
                                href={NotificationController.index.url({ query: {filter:key} })}
                                preserveScroll
                                className={cn(
                                    tabBase,
                                    filter === key ? tabVariants.active : tabVariants.inactive
                                )}
                            >
                                {label}
                                <span className="rounded-full bg-zinc-200 px-2 py-0.5 text-xs dark:bg-zinc-700">
                                    {count}
                                </span>
                            </Link>
                        ))}
                    </div>
                </div>

                {/* Notifications List */}
                <div className="space-y-4">
                    {notifications.length === 0 ? (
                        <Card>
                            <CardContent className="flex flex-col items-center justify-center p-8 text-center">
                                <div className="mb-4 grid h-16 w-16 place-items-center rounded-full bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-800 dark:to-zinc-700">
                                    <Bell
                                        size={24}
                                        className="text-zinc-500 dark:text-zinc-400"
                                    />
                                </div>
                                <h3 className="mb-2 text-lg font-medium text-zinc-900 dark:text-zinc-100">
                                    Nenhuma notificação
                                </h3>
                                <p className="text-sm text-zinc-500 dark:text-zinc-400">
                                    Você está em dia com todas as suas notificações!
                                </p>
                            </CardContent>
                        </Card>
                    ) : (
                        notifications.map((notification) => (
                            <NotificationItem
                                key={notification.id}
                                notification={notification}
                                onClick={handleNotificationClick}
                                hideUnreadDot={filter === 'read'}
                            />
                        ))
                    )}
                </div>

                {/* Pagination */}
                {pagination.total_pages > 1 && (
                    <div className="mt-8">
                        <Pagination
                            pagination={pagination}
                            baseUrl={NotificationController.index.url()}
                            showInfo={true}
                            queryParams={{ filter }}
                        />
                    </div>
                )}
            </div>
        </Layout>
    );
}