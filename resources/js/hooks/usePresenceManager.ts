import { useEffect } from 'react'
import { useEcho } from '@laravel/echo-react'
import { useSetUsers, useAddUser, useRemoveUser, useSetConnected, useClearUsers } from '@/stores/onlineUsersStore'
import { useUpdateHunterOnlineStatus, useRefreshFollowedHunters, useClearFollowedHunters } from '@/stores/followedHuntersStore'
import '@/config/echo'

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
            console.log('🔍 PresenceManager: Sem usuário atual para presença')
            clearUsers()
            return;
        }

        let mounted = true
        let retryTimeout: NodeJS.Timeout

        const setupPresenceChannel = () => {
            if (!mounted) return

            const channel = presence.channel();
            if (!channel) {
                console.log('🔍 PresenceManager: Canal de presença não disponível, tentando novamente em 500ms...')
                setConnected(false)
                retryTimeout = setTimeout(setupPresenceChannel, 500)
                return;
            }

            console.log('🔍 PresenceManager: Configurando canal de presença...')

            try {
                // Configure presence callbacks
                channel
                    .here((users: OnlineUser[]) => {
                        if (!mounted) return
                        console.log('🔍 PresenceManager: Usuários já presentes:', users)
                        setUsers(users)
                        setConnected(true)

                        // Update online status for followed hunters
                        users.forEach(user => {
                            updateHunterOnlineStatus(user.id, true)
                        })
                    })
                    .joining((user: OnlineUser) => {
                        if (!mounted) return
                        console.log('🔍 PresenceManager: Usuário entrou:', user)
                        addUser(user)

                        // Update online status for followed hunter
                        updateHunterOnlineStatus(user.id, true)
                    })
                    .leaving((user: OnlineUser) => {
                        if (!mounted) return
                        console.log('🔍 PresenceManager: Usuário saiu:', user)
                        removeUser(user.id)

                        // Update online status for followed hunter
                        updateHunterOnlineStatus(user.id, false)
                    })
                    .error((error: any) => {
                        if (!mounted) return
                        console.error('🔍 PresenceManager: Erro no canal de presença:', error)
                        setConnected(false)
                        // Retry on error
                        retryTimeout = setTimeout(setupPresenceChannel, 1000)
                    })
            } catch (error) {
                console.error('🔍 PresenceManager: Erro ao configurar canal de presença:', error)
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
            console.log('🔍 PresenceManager: Limpando listeners do canal de presença')
        }
    }, [presence, currentUserId])
}