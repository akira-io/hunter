import NotificationController from '@/actions/App/Http/Controllers/Notification/NotificationController';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Card, CardContent } from '@/components/ui/card';
import { Notification, User } from '@/types';
import { router } from '@inertiajs/react';
import { User as UserIcon } from 'lucide-react';
import React from 'react';

interface NotificationItemProps {
    notification: Notification;
    onClick?: (notification: Notification) => void;
    className?: string;
    hideUnreadDot?: boolean;
}

export const NotificationItem: React.FC<NotificationItemProps> = ({
    notification,
    onClick,
    className = '',
    hideUnreadDot = false,
}) => {
    const isUnread = !notification.read_at;
    const isHuntNotification = notification.data.type === 'hunt_published';
    const follower = notification.data.follower as User;
    const author = notification.data.author as User;
    const displayUser = isHuntNotification ? author : follower;

    const handleMarkAsRead = async () => {
        if (onClick) {
            onClick(notification);
            return;
        }

        // Default click behavior - mark as read
        if (isUnread) {
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

    const handleActionClick = async () => {
        // Mark as read first
        await handleMarkAsRead();

        // Navigate to action URL or profile
        let targetUrl = '';

        if (
            notification.data.action_url &&
            typeof notification.data.action_url === 'string'
        ) {
            targetUrl = notification.data.action_url;
        } else if (displayUser?.id) {
            targetUrl = `/public-profile/${displayUser.id}`;
        }

        if (targetUrl) {
            router.visit(targetUrl);
        }
    };

    const userName = displayUser?.name || 'Usuário';

    return (
        <Card
            className={`dark:hover:bg-zinc-800/50, mt-2 transition-colors hover:border-purple-200 hover:bg-purple-50/50 dark:hover:border-purple-100 dark:hover:bg-purple-900/10 ${
                isUnread
                    ? 'border-purple-200 bg-purple-50/50 dark:border-purple-800 dark:bg-purple-900/10'
                    : ''
            } ${className}`}
            onClick={handleMarkAsRead}
        >
            <CardContent className="p-4">
                <div className="flex items-start gap-3">
                    {/* Avatar - Clickable */}
                    <div
                        className="flex-shrink-0 cursor-pointer"
                        onClick={(e) => {
                            e.stopPropagation();
                            handleActionClick();
                        }}
                    >
                        {displayUser?.avatar_url ? (
                            <Avatar className="h-10 w-10 transition-opacity hover:opacity-80">
                                <AvatarImage
                                    src={displayUser.avatar_url}
                                    alt={displayUser.name}
                                    className="object-cover"
                                />
                                <AvatarFallback>
                                    <UserIcon size={16} />
                                </AvatarFallback>
                            </Avatar>
                        ) : (
                            <div className="grid h-10 w-10 place-items-center rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 transition-opacity hover:opacity-80 dark:from-zinc-700 dark:to-zinc-600">
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
                            {isUnread && !hideUnreadDot && (
                                <div className="h-2 w-2 flex-shrink-0 rounded-full bg-purple-500"></div>
                            )}
                        </div>
                        <p className="mt-1 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                            <span
                                className="cursor-pointer transition-all hover:underline"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    handleActionClick();
                                }}
                            >
                                {userName}
                            </span>
                            {notification.message.replace(userName, '')}
                        </p>
                        <p className="mt-2 text-xs text-zinc-500">
                            {notification.created_at_human}
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
};
