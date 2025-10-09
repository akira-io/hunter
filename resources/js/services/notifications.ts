import { isPWAMode } from '@/hooks/use-pwa';
import api from '@/lib/api';

/**
 * Check if browser/in-app notifications should be shown
 * In PWA mode, we rely on push notifications instead (when configured)
 */
export function shouldShowBrowserNotifications(): boolean {
    // Only disable browser notifications in PWA if VAPID keys are configured
    // Otherwise, keep using browser notifications as fallback
    const hasVapidKey = !!import.meta.env.VITE_VAPID_PUBLIC_KEY;

    if (isPWAMode() && hasVapidKey) {
        // PWA mode with push notifications configured - use push only
        return false;
    }

    // Use browser notifications in all other cases
    return true;
}

/**
 * Show a browser notification (only when not in PWA mode)
 */
export function showBrowserNotification(title: string, options?: NotificationOptions) {
    if (!shouldShowBrowserNotifications()) {
        console.log('[Notifications] Running as PWA, skipping browser notification');
        return;
    }

    if ('Notification' in window && Notification.permission === 'granted') {
        try {
            new Notification(title, options);
        } catch (error) {
            console.error('Failed to show browser notification:', error);
        }
    }
}

export const notificationService = {
    /**
     * Mark a specific notification as read
     */
    markAsRead: async (notificationId: string) => {
        try {
            await api.post(`/notifications/${notificationId}/read`);
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
            throw error;
        }
    },

    /**
     * Mark all notifications as read
     */
    markAllAsRead: async () => {
        try {
            await api.post('/notifications/read-all');
        } catch (error) {
            console.error('Failed to mark all notifications as read:', error);
            throw error;
        }
    },

    /**
     * Get user notifications with pagination
     */
    getNotifications: async (params?: { page?: number; filter?: string }) => {
        try {
            const response = await api.get('/notifications', { params });
            return response.data;
        } catch (error) {
            console.error('Failed to fetch notifications:', error);
            throw error;
        }
    },

    /**
     * Subscribe to push notifications (PWA only)
     */
    subscribeToPush: async () => {
        if (!isPWAMode()) {
            console.log('[Push] Not in PWA mode, skipping push subscription');
            return null;
        }

        try {
            const registration = await navigator.serviceWorker.ready;

            // Check if already subscribed
            const existingSubscription = await registration.pushManager.getSubscription();

            if (existingSubscription) {
                console.log('[Push] Already subscribed');
                return existingSubscription;
            }

            // Subscribe to push notifications
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(import.meta.env.VITE_VAPID_PUBLIC_KEY || ''),
            });

            // Send subscription to backend
            await api.post('/notifications/push/subscribe', {
                subscription: JSON.stringify(subscription),
            });

            console.log('[Push] Subscribed successfully');
            return subscription;
        } catch (error) {
            console.error('[Push] Failed to subscribe:', error);
            throw error;
        }
    },

    /**
     * Unsubscribe from push notifications
     */
    unsubscribeFromPush: async () => {
        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();

            if (subscription) {
                await subscription.unsubscribe();

                // Notify backend
                await api.post('/notifications/push/unsubscribe');

                console.log('[Push] Unsubscribed successfully');
            }
        } catch (error) {
            console.error('[Push] Failed to unsubscribe:', error);
            throw error;
        }
    },

    /**
     * Request notification permission and subscribe if granted
     */
    requestNotificationPermission: async () => {
        if (!('Notification' in window)) {
            console.log('[Notifications] Not supported in this browser');
            return false;
        }

        try {
            const permission = await Notification.requestPermission();

            if (permission === 'granted') {
                console.log('[Notifications] Permission granted');

                // Subscribe to push if in PWA mode
                if (isPWAMode()) {
                    await notificationService.subscribeToPush();
                }

                return true;
            }

            return false;
        } catch (error) {
            console.error('[Notifications] Failed to request permission:', error);
            return false;
        }
    },
};

/**
 * Convert VAPID key from base64 to Uint8Array
 */
function urlBase64ToUint8Array(base64String: string): Uint8Array<ArrayBuffer> {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');

    const rawData = window.atob(base64);
    const buffer = new ArrayBuffer(rawData.length);
    const outputArray = new Uint8Array(buffer);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }

    return outputArray;
}
