import { create } from 'zustand';
import { createJSONStorage, persist } from 'zustand/middleware';

interface Notification {
    id: string;
    type: string;
    title: string;
    message: string;
    data: Record<string, unknown>;
    read_at: string | null;
    created_at: string;
}

interface NotificationState {
    notifications: Notification[];
    unreadCount: number;
    loading: boolean;
    lastUpdated: number;

    // Actions
    setNotifications: (notifications: Notification[]) => void;
    addNotification: (notification: Notification) => void;
    markAsRead: (notificationId: string) => void;
    markAllAsRead: () => void;
    setUnreadCount: (count: number) => void;
    setLoading: (loading: boolean) => void;
    clearNotifications: () => void;
    refreshNotifications: () => Promise<void>;
}

export const useNotificationStore = create<NotificationState>()(
    persist(
        (set, get) => ({
            notifications: [],
            unreadCount: 0,
            loading: false,
            lastUpdated: 0,

            setNotifications: (notifications: Notification[]) => {
                set({
                    notifications,
                    lastUpdated: Date.now(),
                    loading: false,
                });
            },

            addNotification: (notification: Notification) => {
                const { notifications } = get();
                const exists = notifications.some((n) => n.id === notification.id);
                if (!exists) {
                    set({
                        notifications: [notification, ...notifications],
                        lastUpdated: Date.now(),
                        unreadCount: get().unreadCount + (notification.read_at ? 0 : 1),
                    });
                }
            },

            markAsRead: (notificationId: string) => {
                const { notifications } = get();
                const updatedNotifications = notifications.map((notification) =>
                    notification.id === notificationId
                        ? { ...notification, read_at: new Date().toISOString() }
                        : notification
                );
                const wasUnread = notifications.find(n => n.id === notificationId && !n.read_at);
                set({
                    notifications: updatedNotifications,
                    unreadCount: wasUnread ? Math.max(0, get().unreadCount - 1) : get().unreadCount,
                });
            },

            markAllAsRead: () => {
                const { notifications } = get();
                const updatedNotifications = notifications.map((notification) => ({
                    ...notification,
                    read_at: notification.read_at || new Date().toISOString(),
                }));
                set({
                    notifications: updatedNotifications,
                    unreadCount: 0,
                });
            },

            setUnreadCount: (unreadCount: number) => {
                set({ unreadCount });
            },

            setLoading: (loading: boolean) => {
                set({ loading });
            },

            clearNotifications: () => {
                set({
                    notifications: [],
                    unreadCount: 0,
                    loading: false,
                    lastUpdated: 0,
                });
            },

            refreshNotifications: async () => {
                const { setLoading, setNotifications, setUnreadCount } = get();
                setLoading(true);

                try {
                    const response = await fetch('/api/notifications', {
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });

                    if (response.ok) {
                        const data = await response.json();
                        setNotifications(data.notifications || []);
                        setUnreadCount(data.unread_count || 0);
                    } else {
                        setLoading(false);
                    }
                } catch {
                    setLoading(false);
                }
            },
        }),
        {
            name: 'devhunter-notifications',
            storage: createJSONStorage(() => localStorage),

            // Only persist notifications and lastUpdated
            partialize: (state) => ({
                notifications: state.notifications,
                unreadCount: state.unreadCount,
                lastUpdated: state.lastUpdated,
            }),

            // Check if cached data is still valid (5 minutes)
            onRehydrateStorage: () => (state) => {
                if (state) {
                    const now = Date.now();
                    const maxAge = 5 * 60 * 1000; // 5 minutes

                    if (now - state.lastUpdated > maxAge) {
                        state.clearNotifications();
                    } else {
                        // Reset loading on rehydration
                        state.loading = false;
                    }
                }
            },
        },
    ),
);

// Selector helpers for better performance
export const useNotifications = () => useNotificationStore((state) => state.notifications);
export const useUnreadCount = () => useNotificationStore((state) => state.unreadCount);
export const useNotificationLoading = () => useNotificationStore((state) => state.loading);

// Individual action selectors
export const useSetNotifications = () => useNotificationStore((state) => state.setNotifications);
export const useAddNotification = () => useNotificationStore((state) => state.addNotification);
export const useMarkAsRead = () => useNotificationStore((state) => state.markAsRead);
export const useMarkAllAsRead = () => useNotificationStore((state) => state.markAllAsRead);
export const useRefreshNotifications = () => useNotificationStore((state) => state.refreshNotifications);
export const useClearNotifications = () => useNotificationStore((state) => state.clearNotifications);