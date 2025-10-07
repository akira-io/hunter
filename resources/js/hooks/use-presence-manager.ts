import '@/config/echo';
import api from '@/lib/api';
import { useClearFollowedHunters, useRefreshFollowedHunters, useUpdateHunterOnlineStatus } from '@/stores/followedHuntersStore';
import { useClearUsers, useRemoveUser, useSetConnected, useSetUsers } from '@/stores/onlineUsersStore';
import { useEchoPresence } from '@laravel/echo-react';
import { useEffect } from 'react';

interface OnlineUser {
    id: number;
    name: string;
    avatar_url?: string;
    status: 'online' | 'offline';
}

interface UsePresenceManagerProps {
    currentUserId?: number;
}

export const usePresenceManager = ({ currentUserId }: UsePresenceManagerProps) => {
    const setUsers = useSetUsers();
    const removeUser = useRemoveUser();
    const setConnected = useSetConnected();
    const clearUsers = useClearUsers();

    const updateHunterOnlineStatus = useUpdateHunterOnlineStatus();
    const refreshFollowedHunters = useRefreshFollowedHunters();
    const clearFollowedHunters = useClearFollowedHunters();

    useEffect(() => {
        if (currentUserId) {
            refreshFollowedHunters();
        } else {
            clearFollowedHunters();
        }
    }, [currentUserId, refreshFollowedHunters, clearFollowedHunters]);

    useEffect(() => {
        if (!currentUserId) {
            clearUsers();
        }
    }, [currentUserId, clearUsers]);

    const presence = useEchoPresence<OnlineUser>(currentUserId ? 'presence' : '', undefined, undefined, []);

    useEffect(() => {
        if (!currentUserId) {
            clearUsers();
            return;
        }

        const fetchOnlineUsers = async () => {
            try {
                const response = await api.get<{ data: OnlineUser[] }>('/users/online');
                const users = response.data.data;

                setUsers(users);
                setConnected(true);

                // Update hunter online status
                users.forEach((user) => {
                    updateHunterOnlineStatus(user.id, true);
                });
            } catch (error) {
                console.error('Erro ao procurar Hunters online:', error);
                setConnected(false);
            }
        };

        // Initial fetch
        fetchOnlineUsers();

        // Poll every 30 seconds
        const interval = setInterval(fetchOnlineUsers, 30000);

        return () => {
            clearInterval(interval);
        };
    }, [currentUserId, setUsers, setConnected, updateHunterOnlineStatus, clearUsers]);

    useEffect(() => {
        if (!currentUserId) {
            return;
        }

        let mounted = true;
        let retryTimeout: NodeJS.Timeout;

        const setupPresenceChannel = () => {
            if (!mounted) return;

            const channel = presence.channel();
            if (!channel) {
                retryTimeout = setTimeout(setupPresenceChannel, 500);
                return;
            }

            try {
                // Type assertion for presence channel methods
                const presenceChannel = channel as any; // eslint-disable-line @typescript-eslint/no-explicit-any
                presenceChannel
                    .joining((user: OnlineUser) => {
                        if (!mounted) return;

                        // Re-fetch to ensure we only show relevant users
                        api.get<{ data: OnlineUser[] }>('/users/online')
                            .then((response) => {
                                const users = response.data.data;
                                setUsers(users);

                                // Update hunter status if this user is in the list
                                if (users.some((u) => u.id === user.id)) {
                                    updateHunterOnlineStatus(user.id, true);
                                }
                            })
                            .catch(console.error);
                    })
                    .leaving((user: OnlineUser) => {
                        if (!mounted) return;

                        removeUser(user.id);
                        updateHunterOnlineStatus(user.id, false);
                    })
                    .error((error: Error) => {
                        console.log(error);
                        if (!mounted) return;
                        retryTimeout = setTimeout(setupPresenceChannel, 1000);
                    });
            } catch (error) {
                console.error('Erro ao configurar o canal de presença:', error);
                if (mounted) {
                    retryTimeout = setTimeout(setupPresenceChannel, 1000);
                }
            }
        };

        // Start with a small delay to ensure Echo is ready
        retryTimeout = setTimeout(setupPresenceChannel, 100);

        return () => {
            mounted = false;
            if (retryTimeout) {
                clearTimeout(retryTimeout);
            }
        };
    }, [presence, currentUserId]); // eslint-disable-line react-hooks/exhaustive-deps
};
