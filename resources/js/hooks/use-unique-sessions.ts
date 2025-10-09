import { useMemo } from 'react';

interface Session {
    id: number;
    ip_address: string;
    user_agent: string;
    login_at: string;
    location: {
        city?: string;
        country?: string;
    } | null;
    is_current: boolean;
}

/**
 * Custom hook to filter sessions and return only the most recent session per IP address.
 * This prevents visual clutter from multiple sessions with the same IP.
 *
 * @param sessions - Array of active sessions
 * @returns Filtered array with only one session per IP (the most recent)
 */
export function useUniqueSessions(sessions: Session[]): Session[] {
    return useMemo(() => {
        // Filter to show only the most recent session per IP address
        return sessions.reduce((acc, session) => {
            const existingSession = acc.find((s) => s.ip_address === session.ip_address);

            if (!existingSession) {
                // No session with this IP yet, add it
                acc.push(session);
            } else {
                // Compare login times and keep the most recent
                const existingTime = new Date(existingSession.login_at).getTime();
                const currentTime = new Date(session.login_at).getTime();

                if (currentTime > existingTime) {
                    // Replace with more recent session
                    const index = acc.indexOf(existingSession);
                    acc[index] = session;
                }
            }

            return acc;
        }, [] as Session[]);
    }, [sessions]);
}
