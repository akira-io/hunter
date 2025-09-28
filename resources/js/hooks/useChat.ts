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
            return;
        }


        channel
            .listen('.conversations.snapshot', (e: { conversations: Conversation[] }) => {
                setConversations(e.conversations || [])
                setLoading(false)
            })
            .listen('.conversation.created', (e: { conversation: Conversation }) => {
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
            return;
        }


        // Configure message listeners using useEcho hook
        channel
            .listen('.message.sent', (event: { message: Message }) => {
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
            })
            .error((error: any) => {
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
            loadConversations()
        }
    }, [currentUserId, loadConversations])

    const loadConversation = useCallback(async (conversationId: number) => {
        try {
            const response = await fetch(`/conversations/${conversationId}`, {
                headers: getAuthHeaders(),
                credentials: 'same-origin',
            })
            if (response.ok) {
                const data = await response.json()
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
                setActiveConversation(mockConversation)
            }
        } catch (error) {
        }
    }, [])

    const sendMessage = useCallback(async (conversationId: number, content: string, type: 'text' | 'image' | 'file' = 'text', metadata?: Record<string, unknown> | null) => {
        try {
            setSending(true)

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

            // Note: Do NOT add message here manually - let WebSocket handle it
            // This prevents duplicates and ensures real-time works properly

            return message
        } catch (error) {
            throw error
        } finally {
            setSending(false)
        }
    }, [])

    const createConversation = useCallback(async (type: 'direct' | 'group', participants: number[], title?: string) => {
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


            if (!response.ok) {
                const errorText = await response.text()
                throw new Error(`Failed to create conversation: ${response.status} ${errorText}`)
            }

            const data = await response.json()

            await loadConversations()

            return data
        } catch (error) {
            throw error
        }
    }, [loadConversations])

    const markMessagesAsRead = useCallback(async (conversationId: number, messageIds?: number[]) => {
        try {
            console.log('🔍 useChat: Marking messages as read for conversation:', conversationId)
            const response = await fetch(`/conversations/${conversationId}/messages/read`, {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify({
                    message_ids: messageIds,
                }),
            })

            if (response.ok) {
                console.log('🔍 useChat: Successfully marked messages as read')
                setConversations(prev => prev.map(conv =>
                    conv.id === conversationId
                        ? { ...conv, unread_count: 0 }
                        : conv
                ))
            } else {
                console.error('🔍 useChat: Failed to mark messages as read, status:', response.status)
            }
        } catch (error) {
            console.error('🔍 useChat: Failed to mark messages as read:', error)
        }
    }, [])

    const getAuthHeaders = () => {
        const metaTag = document.querySelector('meta[name="csrf-token"]')
        const token = metaTag?.getAttribute('content')

        const headers = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token || '',
            'X-Requested-With': 'XMLHttpRequest',
        }
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