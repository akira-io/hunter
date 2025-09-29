import { useChatContext } from '@/contexts/ChatContext';
import { useOnlineUsers } from '@/stores/onlineUsersStore';
import { useFollowedHunters } from '@/stores/followedHuntersStore';
import { useEcho } from '@laravel/echo-react';
import { Minus, Send, User as UserIcon, X } from 'lucide-react';
import React, { useEffect, useRef, useState } from 'react';

interface Message {
    id: number
    content: string
    type: 'text' | 'image' | 'file'
    metadata?: Record<string, unknown> | null
    created_at: string
    user: {
        id: number
        name: string
        avatar_url?: string
    }
}

interface Conversation {
    id: number
    title: string
    type: 'direct' | 'group'
    participants: Array<{
        id: number
        name: string
        avatar_url?: string
    }>
    messages?: Message[]
    unread_count?: number;
}

interface ChatWindowProps {
    conversationId: number
    currentUserId?: number
}


export const ChatWindow: React.FC<ChatWindowProps> = ({ conversationId, currentUserId }) => {
    const [newMessage, setNewMessage] = useState('')
    const [conversation, setConversation] = useState<Conversation | null>(null)
    const [localLoading, setLocalLoading] = useState(true)
    const [hasInitiallyScrolled, setHasInitiallyScrolled] = useState(false)
    const messagesEndRef = useRef<HTMLDivElement>(null)
    const messagesContainerRef = useRef<HTMLDivElement>(null)
    const textareaRef = useRef<HTMLTextAreaElement>(null)
    const prevMessageCountRef = useRef<number>(0)

    const { closeChatWindow, minimizedWindows, toggleMinimize, sendMessage, sending, markMessagesAsRead, conversations } = useChatContext()

    // Get conversation from centralized state for real-time updates (especially unread_count)
    const centralConversation = conversations.find(c => c.id === conversationId)

    // Create a dedicated WebSocket connection for this specific conversation
    const conversationEcho = useEcho<{ message: Message }>(
        conversationId ? `conversation.${conversationId}` : '',
        undefined,
        undefined,
        [],
        'private'
    );
    const onlineUsers = useOnlineUsers()
    const followedHunters = useFollowedHunters()

    const isMinimized = minimizedWindows.has(conversationId)

    // Get the other participant (for direct conversations)
    const otherParticipant = conversation?.participants.find(p => p.id !== currentUserId)


    // Check if the other participant is online
    const isOtherUserOnline = otherParticipant ?
        onlineUsers.some(user => user.id === otherParticipant.id) ||
        followedHunters.some(hunter => hunter.id === otherParticipant.id && hunter.is_online)
        : false

    // Load conversation locally for this specific ChatWindow
    useEffect(() => {
        const fetchConversation = async () => {
            try {
                setLocalLoading(true)
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                const response = await fetch(`/conversations/${conversationId}`, {
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })

                if (response.ok) {
                    const data = await response.json()
                    setConversation(data)

                    // Scroll to bottom when conversation first loads
                    if (data.messages && data.messages.length > 0) {
                        setTimeout(() => {
                            scrollToBottom()
                            setHasInitiallyScrolled(true)
                        }, 100)
                    }

                    // Mark messages as read when conversation loads and reset unread count
                    await markMessagesAsRead(conversationId);
                }
            } catch (error) {
                console.error('Failed to load conversation:', error);
            } finally {
                setLocalLoading(false)
            }
        }

        fetchConversation()
    }, [conversationId, markMessagesAsRead])

    // Subscribe to WebSocket for this specific conversation
    useEffect(() => {
        const channel = conversationEcho.channel();
        if (!channel) {
            return;
        }

        const messageHandler = (event: { message: Message }) => {
            setConversation(prev => {
                if (!prev) return prev;

                // Check if message already exists (avoid duplicates)
                const messageExists = prev.messages?.some(msg => msg.id === event.message.id)
                if (messageExists) {
                    return prev;
                }

                return {
                    ...prev,
                    messages: [...(prev.messages || []), event.message],
                }
            })
        }

        channel.listen('.message.sent', messageHandler)

        return () => {
            // Laravel Echo React handles cleanup automatically
        }
    }, [conversationEcho, conversationId])

    // Check if user is at bottom of chat (within threshold)
    const isNearBottom = () => {
        const container = messagesContainerRef.current
        if (!container) return true

        const { scrollTop, scrollHeight, clientHeight } = container
        const threshold = 100 // pixels from bottom
        return scrollHeight - scrollTop - clientHeight < threshold
    }

    useEffect(() => {
        const currentMessageCount = conversation?.messages?.length || 0
        const prevMessageCount = prevMessageCountRef.current

        // Only apply smart scrolling rules if chat has been initially loaded
        if (hasInitiallyScrolled) {
            // Only scroll if:
            // 1. Messages were added (not just conversation loaded)
            // 2. Window is not minimized
            // 3. User is already near the bottom (not reading history)
            if (currentMessageCount > prevMessageCount && !isMinimized && isNearBottom()) {
                scrollToBottom()
            }
        }

        prevMessageCountRef.current = currentMessageCount
    }, [conversation?.messages?.length, isMinimized, conversationId, hasInitiallyScrolled])

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' })
    }

    const resetTextareaHeight = () => {
        if (textareaRef.current) {
            textareaRef.current.style.height = 'auto';
            textareaRef.current.style.height = '40px'; // Reset to minimum height
        }
    }

    const handleSendMessage = async (e: React.FormEvent) => {
        e.preventDefault()
        if (!newMessage.trim() || sending) return

        try {
            await sendMessage(conversationId, newMessage.trim())
            setNewMessage('')

            // Reset textarea height to normal
            resetTextareaHeight()

            // Don't add message locally - let WebSocket handle it to avoid duplicates
            // Always scroll when user sends a message (intentional action)
            setTimeout(() => scrollToBottom(), 50)
        } catch (error) {
            console.error('Failed to send message:', error)
        }
    }

    const handleKeyDown = (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleSendMessage(e);
        }
    };


    const getConversationTitle = () => {
        if (!conversation) return 'Loading...'

        if (conversation.title) {
            return conversation.title
        }

        // For direct conversations, show the other person's name
        const otherParticipant = conversation.participants.find(p => p.id !== currentUserId)
        return otherParticipant?.name || 'Unknown User'
    }

    const formatTime = (dateString: string) => {
        const date = new Date(dateString)
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    }

    if (localLoading) {
        return (
            <div className="w-80 bg-white/95 border border-zinc-200 rounded-2xl shadow-2xl backdrop-blur-lg dark:bg-zinc-900/95 dark:border-zinc-700">
                <div className="flex items-center justify-between bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 px-4 py-3 rounded-t-2xl border-b border-zinc-200 dark:border-zinc-700">
                    <span className="text-sm font-medium">Loading...</span>
                </div>
                <div className="h-80 flex items-center justify-center">
                    <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-500"></div>
                </div>
            </div>
        )
    }

    return (
        <div className={`w-80 bg-white/95 border rounded-2xl shadow-2xl backdrop-blur-lg flex flex-col ${isMinimized ? 'h-auto' : 'h-[26rem]'} dark:bg-zinc-900/95 relative ${
            isMinimized && centralConversation?.unread_count && centralConversation.unread_count > 0
                ? 'border-red-300 dark:border-red-700 shadow-red-100 dark:shadow-red-900/20'
                : 'border-zinc-200 dark:border-zinc-700'
        }`}>
            {/* Header */}
            <div
                className={`flex items-center justify-between bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 px-4 py-3 ${isMinimized ? 'rounded-2xl' : 'rounded-t-2xl border-b border-zinc-200 dark:border-zinc-700'} cursor-pointer hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors`}
                onClick={() => {
                    toggleMinimize(conversationId);
                }}
            >
                <div className="flex items-center gap-3">
                    {/* Avatar and Status */}
                    <div className="relative">

                        {otherParticipant?.avatar_url ? (
                            <>
                            <img
                                src={otherParticipant.avatar_url}
                                alt={otherParticipant.name}
                                className="size-8 rounded-full object-cover"
                                onError={(e) => {
                                    const target = e.target as HTMLImageElement;
                                    target.style.display = 'none';
                                    const fallback = target.nextElementSibling as HTMLElement;
                                    if (fallback) fallback.style.display = 'grid';
                                }}
                            />
                                <div
                                    className='size-10 rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-600 grid place-items-center ring-2 ring-white dark:ring-zinc-800'
                                    style={{ display: 'none' }}
                                >
                                    <UserIcon size={20}
                                              className='text-zinc-600 dark:text-zinc-300' />
                                </div>
                            </>
                        ) : (
                            <div className='size-8 rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-600 flex items-center justify-center'>
                                <UserIcon size={16} className='text-zinc-600 dark:text-zinc-300' />
                            </div>
                        )}

                        {/* Status indicator */}
                        <div className={`absolute -bottom-0.5 -right-0.5 size-3 rounded-full border-2 border-zinc-100 dark:border-zinc-800 ${
                            isOtherUserOnline
                                ? 'bg-emerald-400 animate-pulse'
                                : 'bg-zinc-400'
                        }`} />
                    </div>

                    <div className="flex flex-col">
                        <div className='flex items-center gap-2'>
                            <span className='text-sm font-medium truncate'>
                                {getConversationTitle()}
                            </span>
                        </div>
                        <span className="text-xs text-zinc-500 dark:text-zinc-400">
                            {isOtherUserOnline ? 'Online' : 'Offline'}
                        </span>
                    </div>
                </div>
                <div className="flex gap-1">
                    <button
                        onClick={(e) => {
                            e.stopPropagation();
                            toggleMinimize(conversationId);
                        }}
                        className="hover:bg-white/20 p-1.5 rounded-lg transition-colors"
                    >
                        <Minus size={14} />
                    </button>
                    <button
                        onClick={(e) => {
                            e.stopPropagation();
                            closeChatWindow(conversationId);
                        }}
                        className="hover:bg-white/20 p-1.5 rounded-lg transition-colors"
                    >
                        <X size={14} />
                    </button>
                </div>
            </div>

            {/* Messages */}
            {!isMinimized && (
                <>
                    <div
                        ref={messagesContainerRef}
                        className="flex-1 min-h-0 max-h-80 overflow-y-auto p-4 space-y-4 scrollbar-thin scrollbar-thumb-zinc-300 dark:scrollbar-thumb-zinc-600 scrollbar-track-transparent"
                    >
                        {conversation?.messages?.length === 0 ? (
                            <div className="flex flex-col items-center justify-center h-32 text-center">
                                <div className="size-12 rounded-full bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-800 dark:to-zinc-700 grid place-items-center mb-3">
                                    <UserIcon size={20} className="text-zinc-500 dark:text-zinc-400" />
                                </div>
                                <p className="text-sm text-zinc-500 dark:text-zinc-400">
                                    Inicie a conversa!
                                </p>
                            </div>
                        ) : (
                            conversation?.messages?.map((message, index) => {
                                const isOwn = message.user.id === currentUserId

                                return (
                                    <div
                                        key={`${message.id}-${index}-${message.created_at}`}
                                        className={`flex ${isOwn ? 'justify-end' : 'justify-start'}`}
                                    >
                                        <div className={`flex gap-3 max-w-[85%] ${isOwn ? 'flex-row-reverse' : ''}`}>
                                            <div className="flex-shrink-0">
                                                {message.user.avatar_url ? (
                                                    <img
                                                        src={message.user.avatar_url}
                                                        alt={message.user.name}
                                                        className="size-8 rounded-full object-cover ring-2 ring-white dark:ring-zinc-800"
                                                    />
                                                ) : (
                                                    <div className="size-8 rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-600 grid place-items-center ring-2 ring-white dark:ring-zinc-800">
                                                        <UserIcon size={14} className="text-zinc-600 dark:text-zinc-300" />
                                                    </div>
                                                )}
                                            </div>
                                            <div className={`flex flex-col ${isOwn ? 'items-end' : 'items-start'} min-w-0 flex-1`}>
                                                <div
                                                    className={`px-4 py-2.5 rounded-2xl text-sm leading-relaxed break-words whitespace-pre-wrap ${
                                                        isOwn
                                                            ? 'bg-gradient-to-r from-purple-500 to-purple-700 text-white shadow-md'
                                                            : 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 shadow-sm'
                                                    }`}
                                                    style={{
                                                        wordBreak: 'break-word',
                                                        overflowWrap: 'anywhere',
                                                        whiteSpace: 'pre-wrap'
                                                    }}
                                                >
                                                    {message.content}
                                                </div>
                                                <div className={`text-xs text-zinc-500 dark:text-zinc-400 mt-1 ${isOwn ? 'mr-1' : 'ml-1'}`}>
                                                    {formatTime(message.created_at)}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                )
                            })
                        )}
                        <div ref={messagesEndRef} />
                    </div>

                    {/* Message Input */}
                    <form onSubmit={handleSendMessage}
                          className='border-t border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-zinc-50/50 dark:bg-zinc-800/50 rounded-b-2xl'>
                        <div className='flex gap-2 items-end'>
                            <textarea
                                ref={textareaRef}
                                value={newMessage}
                                onChange={(e) => setNewMessage(e.target.value)}
                                onKeyDown={handleKeyDown}
                                placeholder="Digite uma mensagem..."
                                className='flex-1 px-3 py-2 border border-zinc-200 dark:border-zinc-600 rounded-xl bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder-zinc-500 dark:placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent text-sm transition-all resize-none'
                                disabled={sending}
                                rows={1}
                                style={{
                                    minHeight: '40px',
                                    maxHeight: '120px',
                                    height: 'auto'
                                }}
                                onInput={(e) => {
                                    const target = e.target as HTMLTextAreaElement;
                                    target.style.height = 'auto';
                                    target.style.height = Math.min(target.scrollHeight, 120) + 'px';
                                }}
                            />
                            <button
                                type="submit"
                                disabled={!newMessage.trim() || sending}
                                className='px-3 py-2 bg-gradient-to-r from-purple-500 to-purple-700 text-white rounded-xl hover:from-purple-600 hover:to-purple-800 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:from-purple-500 disabled:hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl'
                            >
                                <Send size={16} />
                            </button>
                        </div>
                    </form>
                </>
            )}
        </div>
    )
}