import { useAddNotification, useRefreshNotifications } from '@/stores/notificationStore';
import { useEcho } from '@laravel/echo-react';
import { useEffect } from 'react';

interface UseNotificationManagerProps {
    currentUserId?: number;
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
    created_at: string;
    read_at: string | null;
}

export const useNotificationManager = ({ currentUserId }: UseNotificationManagerProps) => {
    const addNotification = useAddNotification();
    const refreshNotifications = useRefreshNotifications();

    // Use Echo for private notification channel
    const notificationEcho = useEcho<NotificationData>(
        currentUserId ? `App.Models.User.${currentUserId}` : '',
        undefined,
        undefined,
        [],
        'private'
    );

    // Load notifications when user logs in
    useEffect(() => {
        if (currentUserId) {
            refreshNotifications();
        }
    }, [currentUserId, refreshNotifications]);

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
                channel
                    .notification((notification: NotificationData) => {
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
                        });

                        // Show browser notification if permission granted
                        if (Notification.permission === 'granted') {
                            new Notification(notification.title, {
                                body: notification.message,
                                icon: notification.follower?.avatar_url || '/favicon.ico',
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