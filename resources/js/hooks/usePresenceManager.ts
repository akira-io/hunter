import { useEffect } from 'react';
import { useEcho } from '@laravel/echo-react';
import { useAddUser, useClearUsers, useRemoveUser, useSetConnected, useSetUsers } from '@/stores/onlineUsersStore';
import {
    useClearFollowedHunters,
    useRefreshFollowedHunters,
    useUpdateHunterOnlineStatus
} from '@/stores/followedHuntersStore';
import '@/config/echo';

interface OnlineUser {
    id: number
    name: string
    avatar_url?: string
    status: 'online' | 'offline'
}

interface UsePresenceManagerProps {
    currentUserId?: number
}

export const usePresenceManager = ({ currentUserId }: UsePresenceManagerProps) => {
    // Get online users store actions
    const setUsers = useSetUsers()
    const addUser = useAddUser()
    const removeUser = useRemoveUser()
    const setConnected = useSetConnected()
    const clearUsers = useClearUsers()

    // Get followed hunters store actions
    const updateHunterOnlineStatus = useUpdateHunterOnlineStatus()
    const refreshFollowedHunters = useRefreshFollowedHunters()
    const clearFollowedHunters = useClearFollowedHunters()

    // Load followed hunters when user logs in
    useEffect(() => {
        if (currentUserId) {
            console.log('🔍 PresenceManager: Carregando hunters seguidos para usuário:', currentUserId)
            refreshFollowedHunters()
        } else {
            console.log('🔍 PresenceManager: Sem usuário logado, limpando hunters seguidos')
            clearFollowedHunters()
        }
    }, [currentUserId, refreshFollowedHunters, clearFollowedHunters])

    // Clear users when user logs out
    useEffect(() => {
        if (!currentUserId) {
            clearUsers()
        }
    }, [currentUserId, clearUsers])

    // Use useEcho hook for presence channel
    const presence = useEcho<OnlineUser>(
        currentUserId ? 'presence' : '',
        undefined,
        undefined,
        [],
        'presence'
    )

    // Manage presence channel
    useEffect(() => {
        if (!currentUserId) {
            clearUsers()
            return;
        }

        let mounted = true
        let retryTimeout: NodeJS.Timeout

        const setupPresenceChannel = () => {
            if (!mounted) return

            const channel = presence.channel();
            if (!channel) {
                setConnected(false)
                retryTimeout = setTimeout(setupPresenceChannel, 500)
                return;
            }


            try {
                channel
                    .here((users: OnlineUser[]) => {
                        console.log('Test', users);
                        if (!mounted) return
                        setUsers(users)
                        setConnected(true)
                        users.forEach(user => {
                            updateHunterOnlineStatus(user.id, true)
                        })
                    })
                    .joining((user: OnlineUser) => {
                        if (!mounted) return

                        addUser(user)

                        updateHunterOnlineStatus(user.id, true)
                    })
                    .leaving((user: OnlineUser) => {
                        if (!mounted) return

                        removeUser(user.id)

                        updateHunterOnlineStatus(user.id, false)
                    })
                    .error((error: any) => {
                        console.log(error);
                        if (!mounted) return
                        setConnected(false)

                        retryTimeout = setTimeout(setupPresenceChannel, 1000)
                    })
            } catch (error) {
                console.error('Erro ao configurar o canal de presença:', error);
                setConnected(false)
                if (mounted) {
                    retryTimeout = setTimeout(setupPresenceChannel, 1000)
                }
            }
        }

        // Start with a small delay to ensure Echo is ready
        retryTimeout = setTimeout(setupPresenceChannel, 100)

        return () => {
            mounted = false
            if (retryTimeout) {
                clearTimeout(retryTimeout)
            }
        }
    }, [presence, currentUserId])
}