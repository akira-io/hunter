import { useCallback, useEffect, useState } from 'react';
import { useEcho } from '@laravel/echo-react';

interface User {
    id: number
    name: string
    avatar_url?: string
}

interface Message {
    id: number
    content: string
    type: 'text' | 'image' | 'file'
    metadata?: Record<string, unknown> | null
    created_at: string
    user: User
}

interface Conversation {
    id: number
    title: string
    type: 'direct' | 'group'
    participants: User[]
    last_message?: Message
    last_message_at?: string
    unread_count: number
    messages?: Message[]
}

export const useChat = (currentUserId?: number) => {
    const [conversations, setConversations] = useState<Conversation[]>([])
    const [activeConversation, setActiveConversation] = useState<Conversation | null>(null)
    const [loading, setLoading] = useState(true)
    const [sending, setSending] = useState(false)

    // Use useEcho for conversation channel when we have an active conversation
    const conversationEcho = useEcho<{ message: Message }>(
        activeConversation ? `conversation.${activeConversation.id}` : '',
        undefined,
        undefined,
        [],
        'private'
    )

    // Use useEcho for user channel when we have a current user
    const userEcho = useEcho<any>(
        currentUserId ? `user.${currentUserId}` : '',
        undefined,
        undefined,
        [],
        'private'
    )

    // Subscribe to private user channel for websocket-driven bootstrapping (no HTTP)
    useEffect(() => {
        const channel = userEcho.channel();
        if (!channel || !currentUserId) {
            console.log('🔍 useChat: Canal do usuário não disponível', {
                channel: !!channel,
                currentUserId: !!currentUserId
            })
            return;
        }

        console.log('🔍 useChat: Conectando ao canal do usuário:', `user.${currentUserId}`)

        channel
            .listen('.conversations.snapshot', (e: { conversations: Conversation[] }) => {
                console.log('🔍 useChat: Snapshot de conversas recebido:', e.conversations)
                setConversations(e.conversations || [])
                setLoading(false)
            })
            .listen('.conversation.created', (e: { conversation: Conversation }) => {
                console.log('🔍 useChat: Nova conversa criada:', e.conversation)
                setConversations(prev => {
                    const exists = prev.some(c => c.id === e.conversation.id)
                    return exists ? prev : [e.conversation, ...prev]
                })
            })

        return () => {
            // Laravel Echo React handles cleanup automatically
        }
    }, [userEcho, currentUserId])


    useEffect(() => {
        const channel = conversationEcho.channel();
        if (!channel || !activeConversation) {
            console.log('🔍 useChat: Canal da conversa não disponível', {
                channel: !!channel,
                activeConversation: !!activeConversation
            })
            return;
        }

        console.log('🔍 useChat: Conectando ao canal da conversa:', `conversation.${activeConversation.id}`)
        console.log('🔍 useChat: Channel object:', channel)
        console.log('🔍 useChat: Channel state:', channel.state)

        // Configure message listeners using useEcho hook
        channel
            .listen('.message.sent', (event: { message: Message }) => {
                console.log('🔍 useChat: Mensagem recebida via WebSocket:', event)
                setActiveConversation(prev => {
                    if (!prev) return prev
                    return {
                        ...prev,
                        messages: [...(prev.messages || []), event.message],
                    }
                })

                setConversations(prev => prev.map(conv =>
                    conv.id === activeConversation.id
                        ? {
                            ...conv,
                            last_message: event.message,
                            last_message_at: event.message.created_at
                        }
                        : conv
                ))
            })
            .subscribed(() => {
                console.log('🔍 useChat: Successfully subscribed to conversation channel:', `conversation.${activeConversation.id}`)
            })
            .error((error: any) => {
                console.error('🔍 useChat: Erro no canal da conversa:', error)
            })

        return () => {
            // Laravel Echo React handles cleanup automatically
        }
    }, [conversationEcho, activeConversation])

    const loadConversations = useCallback(async () => {
        try {
            setLoading(true)
            const response = await fetch('/conversations', {
                headers: getAuthHeaders(),
                credentials: 'same-origin',
            })
            if (response.ok) {
                const data = await response.json()
                const list = Array.isArray(data) ? data : (data?.data ?? [])
                setConversations(list)
            } else {
                setConversations([])
            }
        } catch (error) {
            console.error('Failed to load conversations:', error)
            setConversations([])
        } finally {
            setLoading(false)
        }
    }, [])

    // Load conversations on mount
    useEffect(() => {
        if (currentUserId) {
            console.log('🔍 useChat: Carregando conversas para o usuário:', currentUserId)
            loadConversations()
        }
    }, [currentUserId, loadConversations])

    const loadConversation = useCallback(async (conversationId: number) => {
        try {
            console.log('🔍 useChat: Carregando conversa:', conversationId)
            const response = await fetch(`/conversations/${conversationId}`, {
                headers: getAuthHeaders(),
                credentials: 'same-origin',
            })
            if (response.ok) {
                const data = await response.json()
                console.log('🔍 useChat: Conversa carregada:', data)
                setActiveConversation(data)
            } else {
                // Fallback to mock conversation
                const mockConversation = {
                    id: conversationId,
                    title: `Debug Chat ${conversationId}`,
                    type: 'direct',
                    participants: [],
                    messages: []
                }
                console.log('🔍 useChat: Usando conversa mock:', mockConversation)
                setActiveConversation(mockConversation)
            }
        } catch (error) {
            console.error('🔍 useChat: Failed to load conversation:', error)
        }
    }, [])

    const sendMessage = useCallback(async (conversationId: number, content: string, type: 'text' | 'image' | 'file' = 'text', metadata?: Record<string, unknown> | null) => {
        try {
            setSending(true)
            console.log('🔍 useChat: Enviando mensagem:', { conversationId, content, type })

            const response = await fetch('/messages', {
                method: 'POST',
                headers: getAuthHeaders(),
                credentials: 'same-origin',
                body: JSON.stringify({
                    conversation_id: conversationId,
                    content,
                    type,
                    metadata,
                }),
            })

            if (!response.ok) {
                throw new Error('Failed to send message')
            }

            const message = await response.json()
            console.log('🔍 useChat: Mensagem enviada com sucesso:', message)

            // Note: Do NOT add message here manually - let WebSocket handle it
            // This prevents duplicates and ensures real-time works properly

            return message
        } catch (error) {
            console.error('🔍 useChat: Failed to send message:', error)
            throw error
        } finally {
            setSending(false)
        }
    }, [])

    const createConversation = useCallback(async (type: 'direct' | 'group', participants: number[], title?: string) => {
        console.log('🔍 useChat: createConversation chamado:', { type, participants, title })
        try {
            const response = await fetch('/conversations', {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify({
                    type,
                    participants,
                    title,
                }),
            })

            console.log('🔍 useChat: Response status:', response.status, response.ok)

            if (!response.ok) {
                const errorText = await response.text()
                console.error('🔍 useChat: Response error:', errorText)
                throw new Error(`Failed to create conversation: ${response.status} ${errorText}`)
            }

            const data = await response.json()
            console.log('🔍 useChat: Conversation data received:', data)

            await loadConversations()
            console.log('🔍 useChat: Conversations reloaded')

            return data
        } catch (error) {
            console.error('🔍 useChat: Failed to create conversation:', error)
            throw error
        }
    }, [loadConversations])

    const markMessagesAsRead = useCallback(async (conversationId: number, messageIds?: number[]) => {
        try {
            await fetch(`/conversations/${conversationId}/messages/read`, {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify({
                    message_ids: messageIds,
                }),
            })

            setConversations(prev => prev.map(conv =>
                conv.id === conversationId
                    ? { ...conv, unread_count: 0 }
                    : conv
            ))
        } catch (error) {
            console.error('Failed to mark messages as read:', error)
        }
    }, [])

    const getAuthHeaders = () => {
        const metaTag = document.querySelector('meta[name="csrf-token"]')
        const token = metaTag?.getAttribute('content')
        console.log('🔍 useChat: CSRF Meta tag found:', !!metaTag)
        console.log('🔍 useChat: CSRF Token:', token)
        console.log('🔍 useChat: CSRF Token length:', token?.length)

        const headers = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token || '',
            'X-Requested-With': 'XMLHttpRequest',
        }
        console.log('🔍 useChat: Headers:', headers)
        return headers
    }

    return {
        conversations,
        activeConversation,
        loading,
        sending,
        loadConversations,
        loadConversation,
        sendMessage,
        createConversation,
        markMessagesAsRead,
        setActiveConversation,
    }
}