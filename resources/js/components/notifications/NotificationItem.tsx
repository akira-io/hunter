import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Card, CardContent } from '@/components/ui/card';
import NotificationController from '@/actions/App/Http/Controllers/Notification/NotificationController';
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
    className = "",
    hideUnreadDot = false
}) => {
    const isUnread = !notification.read_at;
    const follower = notification.data.follower as User;

    const handleMarkAsRead = async () => {
        if (onClick) {
            onClick(notification);
            return;
        }

        // Default click behavior - mark as read
        if (isUnread) {
            router.post(NotificationController.read.url({ id: notification.id }), {}, {
                preserveScroll: true,
                onError: (error) => {
                    console.error('Failed to mark notification as read:', error);
                }
            });
        }
    };

    const handleProfileClick = async () => {
        // Mark as read first
        await handleMarkAsRead();

        // Navigate to follower's profile
        let targetUrl = '';

        if (notification.data.action_url && typeof notification.data.action_url === 'string') {
            targetUrl = notification.data.action_url;
        } else if (follower?.id) {
            targetUrl = `/public-profile/${follower.id}`;
        }

        if (targetUrl) {
            router.visit(targetUrl);
        }
    };

    const followerName = follower?.name || 'Usuário';

    return (
        <Card
            className={`transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50 ${
                isUnread ? 'border-purple-200 bg-purple-50/50 dark:border-purple-800 dark:bg-purple-900/10' : ''
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
                            handleProfileClick();
                        }}
                    >
                        {follower?.avatar_url ? (
                            <Avatar className="h-10 w-10 transition-opacity hover:opacity-80">
                                <AvatarImage
                                    src={follower.avatar_url}
                                    alt={follower.name}
                                    className="object-cover"
                                />
                                <AvatarFallback>
                                    <UserIcon size={16} />
                                </AvatarFallback>
                            </Avatar>
                        ) : (
                            <div className="grid h-10 w-10 place-items-center rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-600 transition-opacity hover:opacity-80">
                                <UserIcon size={16} className="text-zinc-600 dark:text-zinc-300" />
                            </div>
                        )}
                    </div>

                    {/* Content */}
                    <div className="flex-1 min-w-0">
                        <div className="flex items-center gap-2">
                            <h4 className="text-sm font-medium text-zinc-900 dark:text-zinc-100 leading-tight">
                                {notification.title}
                            </h4>
                            {isUnread && !hideUnreadDot && (
                                <div className="h-2 w-2 flex-shrink-0 rounded-full bg-purple-500"></div>
                            )}
                        </div>
                        <p className="mt-1 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            <span
                                className="cursor-pointer hover:underline transition-all"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    handleProfileClick();
                                }}
                            >
                                {followerName}
                            </span>
                            {notification.message.replace(followerName, '')}
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