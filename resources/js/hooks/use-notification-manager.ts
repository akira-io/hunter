import { toast } from '@/hooks/use-toast';
import { useAddNotification, useSetNotifications } from '@/stores/notificationStore';
import { useEcho } from '@laravel/echo-react';
import { useEffect } from 'react';

interface UseNotificationManagerProps {
    currentUserId?: number;
    notifications?: Array<{
        id: string;
        type: string;
        title: string;
        message: string;
        data: Record<string, unknown>;
        read_at: string | null;
        created_at: string;
        created_at_human: string;
    }>;
    unreadCount?: number;
}

interface NotificationData {
    id: string;
    type: string;
    title: string;
    message: string;
    follower?: {
        id: number;
        name: string;
        username: string;
        avatar_url?: string;
    };
    author?: {
        id: number;
        name: string;
        username: string;
        avatar_url?: string;
    };
    hunt?: {
        id: number;
        title: string;
        content: string;
    };
    created_at: string;
    created_at_human: string;
    read_at: string | null;
}

export const useNotificationManager = ({ currentUserId, notifications }: UseNotificationManagerProps) => {
    const addNotification = useAddNotification();
    const setNotifications = useSetNotifications();

    // Use Echo for private notification channel
    const notificationEcho = useEcho<NotificationData>(currentUserId ? `App.Models.User.${currentUserId}` : '', undefined, undefined, [], 'private');

    // Sync Inertia notifications data with store
    useEffect(() => {
        if (notifications) {
            setNotifications(notifications);
        }
    }, [notifications, setNotifications]);

    // Listen for real-time notifications
    useEffect(() => {
        const channel = notificationEcho.channel();
        if (!channel || !currentUserId) {
            return;
        }

        let mounted = true;

        const setupNotificationChannel = () => {
            if (!mounted) return;

            try {
                channel.notification((notification: NotificationData) => {
                    if (!mounted) return;

                    // Add notification to store
                    addNotification({
                        id: notification.id,
                        type: notification.type,
                        title: notification.title,
                        message: notification.message,
                        data: notification as unknown as Record<string, unknown>,
                        read_at: notification.read_at,
                        created_at: notification.created_at,
                        created_at_human: notification.created_at_human,
                    });

                    // Show toast notification for in-app feedback
                    toast({
                        title: notification.title,
                        description: notification.message,
                        duration: 5000,
                    });

                    // Show browser notification if permission granted
                    if (Notification.permission === 'granted') {
                        const avatarUrl = notification.follower?.avatar_url || notification.author?.avatar_url || '/favicon.ico';
                        new Notification(notification.title, {
                            body: notification.message,
                            icon: avatarUrl,
                            tag: `notification-${notification.id}`,
                        });
                    }
                });
            } catch (error) {
                console.error('Error setting up notification channel:', error);
            }
        };

        setupNotificationChannel();

        return () => {
            mounted = false;
        };
    }, [notificationEcho, currentUserId, addNotification]);

    // Request notification permission
    useEffect(() => {
        if (currentUserId && 'Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }, [currentUserId]);
};
