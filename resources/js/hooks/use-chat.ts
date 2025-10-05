import { useEcho } from '@laravel/echo-react';
import { useCallback, useEffect, useRef, useState } from 'react';

interface User {
    id: number;
    name: string;
    avatar_url?: string;
}

interface Message {
    id: number;
    content: string;
    type: 'text' | 'image' | 'file';
    metadata?: Record<string, unknown> | null;
    created_at: string;
    user: User;
}

interface MessageEvent {
    message: Message;
    conversation_id: number;
}

interface Conversation {
    id: number;
    title: string;
    type: 'direct' | 'group';
    participants: User[];
    last_message?: Message;
    last_message_at?: string;
    unread_count: number;
    messages?: Message[];
}

export const useChat = (currentUserId?: number, chatWindows?: number[], minimizedWindows?: Set<number>) => {
    const [conversations, setConversations] = useState<Conversation[]>([]);
    const [activeConversation, setActiveConversation] = useState<Conversation | null>(null);
    const [loading, setLoading] = useState(true);
    const [sending, setSending] = useState(false);
    const messageHandlerRef = useRef<((event: MessageEvent) => void) | null>(null);

    // Use useEcho for user channel when we have a current user
    const userEcho = useEcho<MessageEvent>(currentUserId ? `user.${currentUserId}` : '', undefined, undefined, [], 'private');

    // Use useEcho for conversation channel when we have an active conversation
    const conversationEcho = useEcho<{ message: Message }>(
        activeConversation ? `conversation.${activeConversation.id}` : '',
        undefined,
        undefined,
        [],
        'private',
    );

    // Listen to all messages via user channel instead of individual conversation channels
    useEffect(() => {
        const channel = userEcho.channel();
        if (!channel || !currentUserId) {
            return;
        }

        // Remove any existing listener first
        if (messageHandlerRef.current) {
            channel.stopListening('.message.sent', messageHandlerRef.current);
        }

        // Listen for messages on the user channel
        const messageHandler = (event: { message: Message; conversation_id: number }) => {
            const isMyMessage = event.message.user.id === currentUserId;
            if (isMyMessage) {
                return; // Don't increment for our own messages
            }

            // Update conversations list - increment unread count for non-active conversations
            setConversations((prev) =>
                prev.map((conv) => {
                    if (conv.id === event.conversation_id) {
                        // Check if this conversation is currently active (using state from closure)
                        const isActiveConversation = activeConversation?.id === event.conversation_id;

                        // Check if there's an open (non-minimized) chat window for this conversation
                        const hasOpenChatWindow = chatWindows?.includes(event.conversation_id) && !minimizedWindows?.has(event.conversation_id);

                        if (isActiveConversation || hasOpenChatWindow) {
                            return {
                                ...conv,
                                last_message: event.message,
                                last_message_at: event.message.created_at,
                                // Don't increment unread_count for active conversation or open chat window
                            };
                        }
                        return {
                            ...conv,
                            last_message: event.message,
                            last_message_at: event.message.created_at,
                            unread_count: (conv.unread_count || 0) + 1,
                        };
                    }
                    return conv;
                }),
            );
        };

        // Store reference and set up listener
        messageHandlerRef.current = messageHandler;
        channel.listen('.message.sent', messageHandler);

        return () => {
            if (messageHandlerRef.current) {
                channel.stopListening('.message.sent', messageHandlerRef.current);
                messageHandlerRef.current = null;
            }
        };
    }, [userEcho, currentUserId]); // eslint-disable-line react-hooks/exhaustive-deps

    // Subscribe to private user channel for websocket-driven bootstrapping (no HTTP)
    useEffect(() => {
        const channel = userEcho.channel();
        if (!channel || !currentUserId) {
            return;
        }

        channel
            .listen('.conversations.snapshot', (e: { conversations: Conversation[] }) => {
                setConversations(e.conversations || []);
                setLoading(false);
            })
            .listen('.conversation.created', (e: { conversation: Conversation }) => {
                setConversations((prev) => {
                    const exists = prev.some((c) => c.id === e.conversation.id);
                    return exists ? prev : [e.conversation, ...prev];
                });
            });

        return () => {
            // Laravel Echo React handles cleanup automatically
        };
    }, [userEcho, currentUserId]);

    useEffect(() => {
        const channel = conversationEcho.channel();
        if (!channel || !activeConversation) {
            return;
        }

        // Configure message listeners using useEcho hook
        channel
            .listen('.message.sent', (event: { message: Message }) => {
                setActiveConversation((prev) => {
                    if (!prev) return prev;
                    return {
                        ...prev,
                        messages: [...(prev.messages || []), event.message],
                    };
                });

                setConversations((prev) =>
                    prev.map((conv) =>
                        conv.id === activeConversation.id
                            ? {
                                  ...conv,
                                  last_message: event.message,
                                  last_message_at: event.message.created_at,
                                  // Don't increment unread_count for active conversation
                                  unread_count: conv.unread_count,
                              }
                            : conv,
                    ),
                );
            })
            .subscribed(() => {})
            .error((error: Error) => {
                console.error('Echo channel error:', error);
            });

        return () => {
            // Laravel Echo React handles cleanup automatically
        };
    }, [conversationEcho, activeConversation]);

    const loadConversations = useCallback(async () => {
        try {
            setLoading(true);
            const response = await fetch('/conversations', {
                headers: getAuthHeaders(),
                credentials: 'same-origin',
            });
            if (response.ok) {
                const data = await response.json();
                const list = Array.isArray(data) ? data : (data?.data ?? []);
                setConversations(list);
            } else {
                setConversations([]);
            }
        } catch (error) {
            console.error('Failed to load conversations:', error);
            setConversations([]);
        } finally {
            setLoading(false);
        }
    }, []);

    // Load conversations on mount
    useEffect(() => {
        if (currentUserId) {
            loadConversations();
        }
    }, [currentUserId, loadConversations]);

    const loadConversation = useCallback(async (conversationId: number) => {
        try {
            const response = await fetch(`/conversations/${conversationId}`, {
                headers: getAuthHeaders(),
                credentials: 'same-origin',
            });
            if (response.ok) {
                const data = await response.json();
                setActiveConversation(data);
            } else {
                // Fallback to mock conversation
                const mockConversation = {
                    id: conversationId,
                    title: `Debug Chat ${conversationId}`,
                    type: 'direct' as const,
                    participants: [],
                    messages: [],
                    unread_count: 0,
                };
                setActiveConversation(mockConversation);
            }
        } catch (error) {
            console.error('Failed to mark messages as read:', error);
        }
    }, []);

    const sendMessage = useCallback(
        async (conversationId: number, content: string, type: 'text' | 'image' | 'file' = 'text', metadata?: Record<string, unknown> | null) => {
            try {
                setSending(true);

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
                });

                if (!response.ok) {
                    throw new Error('Failed to send message');
                }

                // Note: Do NOT return the message - let WebSocket handle it
                // This prevents duplicates and ensures real-time works properly
            } catch (error) {
                console.error('Failed to send message:', error);
                throw error;
            } finally {
                setSending(false);
            }
        },
        [],
    );

    const createConversation = useCallback(
        async (type: 'direct' | 'group', participants: number[], title?: string) => {
            try {
                const response = await fetch('/conversations', {
                    method: 'POST',
                    headers: getAuthHeaders(),
                    body: JSON.stringify({
                        type,
                        participants,
                        title,
                    }),
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    const errorMessage = errorData.error || 'Failed to create conversation';

                    // Create a custom error with status code
                    const error = new Error(errorMessage) as Error & { status?: number };
                    error.status = response.status;
                    throw error;
                }

                const data = await response.json();

                await loadConversations();

                return data;
            } catch (error) {
                console.error('Failed to create conversation:', error);
                throw error;
            }
        },
        [loadConversations],
    );

    const markMessagesAsRead = useCallback(async (conversationId: number, messageIds?: number[]) => {
        try {
            const response = await fetch(`/conversations/${conversationId}/messages/read`, {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify({
                    message_ids: messageIds,
                }),
            });

            if (response.ok) {
                setConversations((prev) => prev.map((conv) => (conv.id === conversationId ? { ...conv, unread_count: 0 } : conv)));
            }
        } catch (error) {
            console.error('Failed to mark messages as read:', error);
        }
    }, []);

    const getAuthHeaders = () => {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        const token = metaTag?.getAttribute('content');

        const headers = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token || '',
            'X-Requested-With': 'XMLHttpRequest',
        };
        return headers;
    };

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
    };
};
