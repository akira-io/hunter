import api from '@/lib/api';

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
};