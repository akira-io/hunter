import api from '@/lib/api';
import chat from '@/routes/chat';
import { router } from '@inertiajs/react';
import { useState } from 'react';

interface StartConversationResult {
    startConversation: (userId: number) => Promise<void>;
    isStarting: boolean;
    error: string | null;
}

export const useStartConversation = (): StartConversationResult => {
    const [isStarting, setIsStarting] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const startConversation = async (userId: number) => {
        setIsStarting(true);
        setError(null);

        try {
            const response = await api.post<{ id: number; message: string }>('/conversations', {
                type: 'direct',
                participants: [userId],
            });

            const conversationId = response.data.id;

            router.visit(chat.show(conversationId).url, {
                preserveScroll: true,
                preserveState: false,
            });
        } catch (err) {
            console.error('Error starting conversation:', err);
            setError('Failed to start conversation. Please try again.');
        } finally {
            setIsStarting(false);
        }
    };

    return {
        startConversation,
        isStarting,
        error,
    };
};
