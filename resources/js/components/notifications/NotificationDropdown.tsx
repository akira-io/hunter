import NotificationController from '@/actions/App/Http/Controllers/Notification/NotificationController';
import {
    useMarkAllAsRead,
    useMarkAsRead,
    useNotificationLoading,
    useNotifications,
    useUnreadCount,
} from '@/stores/notificationStore';
import { User } from '@/types';
import { router } from '@inertiajs/react';
import { Bell, Check, User as UserIcon, X } from 'lucide-react';
import React, { useEffect, useRef } from 'react';

interface NotificationDropdownProps {
    isOpen: boolean;
    onClose: () => void;
}

export const NotificationDropdown: React.FC<NotificationDropdownProps> = ({
    isOpen,
    onClose,
}) => {
    const dropdownRef = useRef<HTMLDivElement>(null);
    const notifications = useNotifications();
    const loading = useNotificationLoading();
    const unreadCount = useUnreadCount();
    const markAsRead = useMarkAsRead();
    const markAllAsRead = useMarkAllAsRead();

    // Close dropdown when clicking outside
    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (
                dropdownRef.current &&
                !dropdownRef.current.contains(event.target as Node)
            ) {
                onClose();
            }
        };

        if (isOpen) {
            document.addEventListener('mousedown', handleClickOutside);
        }

        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, [isOpen, onClose]);

    const handleMarkAsRead = async (notification: {
        id: string;
        read_at: string | null;
    }) => {
        // Mark as read if unread
        if (!notification.read_at) {
            markAsRead(notification.id);

            // Also mark as read on server
            router.post(
                NotificationController.read.url({ id: notification.id }),
                {},
                {
                    preserveScroll: true,
                    onError: (error) => {
                        console.error(
                            'Failed to mark notification as read:',
                            error,
                        );
                    },
                },
            );
        }
    };

    const handleProfileClick = async (notification: {
        id: string;
        read_at: string | null;
        data: Record<string, unknown>;
    }) => {
        // Mark as read first
        await handleMarkAsRead(notification);

        // Navigate to follower's profile
        let targetUrl = '';

        if (
            notification.data.action_url &&
            typeof notification.data.action_url === 'string'
        ) {
            targetUrl = notification.data.action_url;
        } else if (
            notification.data.follower &&
            typeof notification.data.follower === 'object' &&
            'id' in notification.data.follower &&
            notification.data.follower.id
        ) {
            targetUrl = `/public-profile/${notification.data.follower.id}`;
        }

        if (targetUrl) {
            onClose();
            router.visit(targetUrl);
        }
    };

    const handleMarkAllAsRead = () => {
        markAllAsRead();

        router.post(
            NotificationController.readAll.url(),
            {},
            {
                preserveScroll: true,
                onError: (error) => {
                    console.error(
                        'Failed to mark all notifications as read:',
                        error,
                    );
                },
            },
        );
    };

    const handleNavigateToNotifications = () => {
        window.location.href = '/notifications';
    };

    // Filter to show only unread notifications
    const unreadNotifications = notifications.filter(
        (notification) => !notification.read_at,
    );

    // Render notification list content (shared between drawer and dropdown)
    const renderNotificationContent = () => (
        <>
            {loading ? (
                <div className="flex items-center justify-center p-8">
                    <div className="h-6 w-6 animate-spin rounded-full border-b-2 border-purple-500"></div>
                </div>
            ) : unreadNotifications.length === 0 ? (
                <div className="flex flex-col items-center justify-center p-8 text-center">
                    <div className="mb-4 grid h-12 w-12 place-items-center rounded-full bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-800 dark:to-zinc-700">
                        <Bell
                            size={20}
                            className="text-zinc-500 dark:text-zinc-400"
                        />
                    </div>
                    <p className="mb-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                        Nenhuma notificação
                    </p>
                    <p className="text-xs text-zinc-500 dark:text-zinc-400">
                        Você está em dia!
                    </p>
                </div>
            ) : (
                <div className="divide-y divide-zinc-200 dark:divide-zinc-700">
                    {unreadNotifications.map((notification) => {
                        const follower = notification.data.follower as User;
                        const followerName = follower?.name || 'Utilizador';

                        return (
                            <div
                                key={notification.id}
                                onClick={() => handleMarkAsRead(notification)}
                                className="bg-blue-50/50 p-4 transition-colors hover:bg-zinc-50 active:bg-zinc-100 dark:bg-blue-900/10 dark:hover:bg-zinc-700/50 dark:active:bg-zinc-700"
                                data-testid="notification-item"
                            >
                                <div className="flex items-start gap-3">
                                    {/* Avatar - Clickable */}
                                    <div
                                        className="flex-shrink-0 cursor-pointer"
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            handleProfileClick(notification);
                                        }}
                                    >
                                        {follower?.avatar_url ? (
                                            <img
                                                src={follower.avatar_url}
                                                alt={followerName}
                                                className="h-10 w-10 rounded-full object-cover transition-opacity hover:opacity-80 active:opacity-70"
                                            />
                                        ) : (
                                            <div className="grid h-10 w-10 place-items-center rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 transition-opacity hover:opacity-80 active:opacity-70 dark:from-zinc-700 dark:to-zinc-600">
                                                <UserIcon
                                                    size={16}
                                                    className="text-zinc-600 dark:text-zinc-300"
                                                />
                                            </div>
                                        )}
                                    </div>

                                    {/* Content */}
                                    <div className="min-w-0 flex-1">
                                        <div className="flex items-center gap-2">
                                            <h4 className="text-sm leading-tight font-medium text-zinc-900 dark:text-zinc-100">
                                                {notification.title}
                                            </h4>
                                            <div className="h-2 w-2 flex-shrink-0 rounded-full bg-blue-500"></div>
                                        </div>
                                        <p className="mt-1 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                                            <span
                                                className="cursor-pointer font-medium transition-all hover:underline active:text-zinc-900 dark:active:text-zinc-200"
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    handleProfileClick(
                                                        notification,
                                                    );
                                                }}
                                            >
                                                {followerName}
                                            </span>
                                            {notification.message.replace(
                                                followerName,
                                                '',
                                            )}
                                        </p>
                                        <p className="mt-2 text-xs text-zinc-500">
                                            {notification.created_at_human}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            )}
        </>
    );

    // Render header actions (shared between drawer and dropdown)
    const renderHeaderActions = () => (
        <div className="flex items-center gap-2">
            {unreadCount > 0 && (
                <button
                    onClick={handleMarkAllAsRead}
                    className="flex items-center gap-1 rounded-md px-2 py-1.5 text-xs font-medium text-blue-600 transition-colors hover:bg-blue-50 active:bg-blue-100 dark:text-blue-400 dark:hover:bg-blue-900/20 dark:active:bg-blue-900/30"
                >
                    <Check size={14} />
                    <span className="hidden sm:inline">Marcar todas</span>
                    <span className="sm:hidden">Todas</span>
                </button>
            )}
            <button
                onClick={onClose}
                className="rounded-md p-1.5 text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-700 active:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-zinc-300 dark:active:bg-zinc-600"
                aria-label="Fechar notificações"
            >
                <X size={18} />
            </button>
        </div>
    );

    if (!isOpen) return null;

    return (
        <div
            ref={dropdownRef}
            className="gradient absolute top-12 right-0 z-50 hidden w-96 max-w-[calc(100vw-2rem)] flex-col rounded-lg border border-zinc-200 bg-card shadow-lg sm:flex dark:border-zinc-700"
            data-testid="notification-dropdown"
        >
            {/* Header */}
            <div className="flex shrink-0 items-center justify-between border-b border-zinc-200 p-4 dark:border-zinc-700">
                <h3 className="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                    Notificações
                </h3>
                {renderHeaderActions()}
            </div>

            {/* Content */}
            <div className="max-h-80 flex-1 overflow-y-auto overscroll-contain">
                {renderNotificationContent()}
            </div>

            {/* Footer */}
            <div className="shrink-0 border-t border-zinc-200 p-3 dark:border-zinc-700">
                <button
                    type="button"
                    onClick={handleNavigateToNotifications}
                    className="block w-full rounded-md bg-zinc-100 px-3 py-2.5 text-center text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-200 active:bg-zinc-300 dark:bg-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-600 dark:active:bg-zinc-500"
                >
                    Ver todas as notificações
                </button>
            </div>
        </div>
    );
};
