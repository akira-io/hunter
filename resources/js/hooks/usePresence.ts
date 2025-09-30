import Echo from '@/config/echo';
import { useEffect } from 'react';

interface UsePresenceProps {
    userId?: number;
}

// Joins the Reverb presence channel so the backend (and other clients) know this user is online.
// No HTTP heartbeats; membership is maintained by the websocket connection.
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
