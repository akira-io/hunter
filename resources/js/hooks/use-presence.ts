import Echo from '@/config/echo';
import { useEffect } from 'react';

interface UsePresenceProps {
    userId?: number;
}

export const usePresence = ({ userId }: UsePresenceProps) => {
    useEffect(() => {
        if (!userId) return;

        const presence = Echo?.join?.('presence');

        return () => {
            try {
                // Echo presence handles disconnect automatically
                presence?.leave?.();
            } catch {
                // noop
            }
        };
    }, [userId]);
};
