import { useChatContext } from '@/contexts/ChatContext';
import { useChat } from '@/hooks/useChat';
import { shouldUseMobileChat } from '@/hooks/useDeviceDetection';
import { router } from '@inertiajs/react';
import { MessageCircle, User as UserIcon } from 'lucide-react';
import React from 'react';

interface User {
    id: number
    name: string
    avatar_url?: string
}

interface Conversation {
    id: number
    title: string
    type: 'direct' | 'group'
    participants: User[]
    last_message?: {
        id: number
        content: string
        created_at: string
        user: User
    }
    last_message_at?: string
    unread_count: number
    other_participant?: User
}

interface ConversationsListProps {
    currentUserId?: number
}

export const ConversationsList: React.FC<ConversationsListProps> = ({ currentUserId }) => {
    const { conversations, loading } = useChat(currentUserId)
    const { openChatWindow } = useChatContext()

    const handleConversationClick = (conversationId: number) => {
        if (shouldUseMobileChat()) {
            router.visit(`/chat/mobile/${conversationId}`)
        } else {
            openChatWindow(conversationId)
        }
    }

    const formatTime = (dateString?: string) => {
        if (!dateString) return ''
        const date = new Date(dateString)
        const now = new Date()
        const diffTime = Math.abs(now.getTime() - date.getTime())
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24))

        if (diffDays === 0) {
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        } else if (diffDays === 1) {
            return 'Yesterday'
        } else if (diffDays < 7) {
            return date.toLocaleDateString([], { weekday: 'short' })
        } else {
            return date.toLocaleDateString([], { month: 'short', day: 'numeric' })
        }
    }

    const getConversationTitle = (conversation: Conversation) => {
        if (conversation.title) {
            return conversation.title
        }

        return conversation.other_participant?.name || 'Unknown User'
    }

    const getConversationAvatar = (conversation: Conversation) => {
        if (conversation.type === 'group') {
            return (
                <div className="w-12 h-12 rounded-full bg-blue-500 flex items-center justify-center">
                    <MessageCircle size={20} className="text-white" />
                </div>
            )
        }

        const otherParticipant = conversation.other_participant

        if (otherParticipant?.avatar_url) {
            return (
                <div className="relative w-12 h-12">
                    <img
                        src={otherParticipant.avatar_url}
                        alt={otherParticipant.name}
                        className="w-12 h-12 rounded-full object-cover"
                        onError={(e) => {
                            e.currentTarget.style.display = 'none'
                            const fallback = e.currentTarget.parentElement?.querySelector('.avatar-fallback') as HTMLElement
                            if (fallback) {
                                fallback.style.display = 'flex'
                            }
                        }}
                    />
                    <div className="avatar-fallback absolute inset-0 w-12 h-12 rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-600 flex items-center justify-center" style={{ display: 'none' }}>
                        <UserIcon size={20} className="text-zinc-600 dark:text-zinc-300" />
                    </div>
                </div>
            )
        }

        return (
            <div className="w-12 h-12 rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-600 flex items-center justify-center">
                <UserIcon size={20} className="text-zinc-600 dark:text-zinc-300" />
            </div>
        )
    }

    if (loading) {
        return (
            <div className="p-4">
                <div className="animate-pulse space-y-4">
                    {[...Array(5)].map((_, i) => (
                        <div key={i} className="flex items-center gap-3">
                            <div className="w-12 h-12 bg-gray-200 rounded-full"></div>
                            <div className="flex-1">
                                <div className="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                                <div className="h-3 bg-gray-200 rounded w-1/2"></div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        )
    }

    return (
        <div className="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div className="p-4 border-b border-gray-200">
                <h2 className="text-lg font-semibold text-gray-900">Messages</h2>
            </div>
            <div className="max-h-96 overflow-y-auto">
                {conversations.length === 0 ? (
                    <div className="p-8 text-center text-gray-500">
                        <MessageCircle size={48} className="mx-auto mb-4 text-gray-300" />
                        <p>No conversations yet</p>
                        <p className="text-sm">Start chatting with someone!</p>
                    </div>
                ) : (
                    <div>
                        {conversations.map((conversation) => (
                            <div
                                key={conversation.id}
                                className="flex items-center gap-3 p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                onClick={() => handleConversationClick(conversation.id)}
                            >
                                <div className="flex-shrink-0 relative">
                                    {getConversationAvatar(conversation)}
                                    {conversation.unread_count > 0 && (
                                        <div className="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                            {conversation.unread_count > 99 ? '99+' : conversation.unread_count}
                                        </div>
                                    )}
                                </div>
                                <div className="flex-1 min-w-0">
                                    <div className="flex items-center justify-between">
                                        <h3 className="text-sm font-medium text-gray-900 truncate">
                                            {getConversationTitle(conversation)}
                                        </h3>
                                        <span className="text-xs text-gray-500">
                                            {formatTime(conversation.last_message_at)}
                                        </span>
                                    </div>
                                    {conversation.last_message && (
                                        <p className="text-sm text-gray-600 truncate mt-1">
                                            {conversation.last_message.user.id === currentUserId && 'You: '}
                                            {conversation.last_message.content}
                                        </p>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </div>
    )
}