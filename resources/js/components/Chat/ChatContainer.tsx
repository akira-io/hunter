import { useChatContext } from '@/contexts/ChatContext';
import { useChat } from '@/hooks/useChat';
import { shouldUseMobileChat } from '@/hooks/useDeviceDetection';
import { router } from '@inertiajs/react';
import { User as UserIcon, X } from 'lucide-react';
import React from 'react';
import { ChatWindow } from './ChatWindow';

interface ChatContainerProps {
    currentUserId?: number
}

export const ChatContainer: React.FC<ChatContainerProps> = ({ currentUserId }) => {
    const { chatWindows, backgroundWindows, switchToWindow, closeBackgroundWindow } = useChatContext()
    const { conversations } = useChat(currentUserId)

    const getConversationTitle = (conversationId: number) => {
        const conv = conversations.find(c => c.id === conversationId)
        if (!conv) return `Chat ${conversationId}`

        if (conv.title) return conv.title

        const otherParticipant = conv.participants.find(p => p.id !== currentUserId)
        return otherParticipant?.name || 'Unknown User'
    }

    const getUnreadCount = (conversationId: number) => {
        const conv = conversations.find(c => c.id === conversationId)
        return conv?.unread_count || 0
    }

    const getOtherParticipant = (conversationId: number) => {
        const conv = conversations.find(c => c.id === conversationId)
        if (!conv) return null
        return conv.participants.find(p => p.id !== currentUserId)
    }

    return (
        <div className="fixed bottom-6 right-24 z-40 flex items-end gap-2">
            {/* Background chat tabs - stacked vertically on the left */}
            {backgroundWindows.length > 0 && (
                <div className="flex flex-col gap-1 items-end">
                    {backgroundWindows.map((conversationId) => {
                        const unreadCount = getUnreadCount(conversationId)
                        const otherParticipant = getOtherParticipant(conversationId)
                        return (
                            <div
                                key={conversationId}
                                className="group relative bg-zinc-800/90 hover:bg-zinc-700/90 text-white rounded-lg text-xs font-medium transition-all duration-200 hover:scale-105 shadow-lg backdrop-blur-sm border border-zinc-600/50 w-48"
                            >
                                <button
                                    onClick={() => {
                                        if (shouldUseMobileChat()) {
                                            router.visit(`/chat/mobile/${conversationId}`)
                                        } else {
                                            switchToWindow(conversationId)
                                        }
                                    }}
                                    className="flex items-center gap-2 px-3 py-2 pr-8 w-full text-left"
                                >
                                    {/* Avatar */}
                                    <div className="relative shrink-0">
                                        {otherParticipant?.avatar_url ? (
                                            <>
                                                <img
                                                src={otherParticipant.avatar_url}
                                                alt={otherParticipant.name}
                                                className='size-6 rounded-full object-cover ring-1 ring-white/20'
                                                onError={(e) => {
                                                    const target = e.target as HTMLImageElement;
                                                    target.style.display = 'none';
                                                    const fallback = target.nextElementSibling as HTMLElement;
                                                    if (fallback) fallback.style.display = 'grid';
                                                }} />
                                                <div
                                                    className='size-6 rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-600 grid place-items-center ring-2 ring-white dark:ring-zinc-800'
                                                    style={{ display: 'none' }}
                                                >
                                                    <UserIcon size={10}
                                                              className='text-zinc-600 dark:text-zinc-300' />
                                                </div>
                                            </>
                                        ) : (
                                            <div className='size-6 rounded-full bg-gradient-to-br from-zinc-300 to-zinc-400 dark:from-zinc-600 dark:to-zinc-700 flex items-center justify-center ring-1 ring-white/20'>
                                                <UserIcon size={12} className='text-zinc-600 dark:text-zinc-300' />
                                            </div>
                                        )}
                                    </div>

                                    <span className="truncate flex-1">
                                        {getConversationTitle(conversationId)}
                                    </span>
                                    {unreadCount > 0 && (
                                        <div className="bg-red-500 text-white rounded-full min-w-[16px] h-4 px-1 text-xs font-bold flex items-center justify-center shrink-0">
                                            {unreadCount > 9 ? '9+' : unreadCount}
                                        </div>
                                    )}
                                </button>
                                <button
                                    onClick={(e) => {
                                        e.stopPropagation()
                                        closeBackgroundWindow(conversationId)
                                    }}
                                    className="absolute right-1 top-1/2 -translate-y-1/2 p-1 hover:bg-zinc-600/50 rounded-full transition-colors opacity-60 hover:opacity-100"
                                >
                                    <X size={10} />
                                </button>
                            </div>
                        )
                    })}
                </div>
            )}

            {/* Active chat window */}
            <div className="flex items-end">
                {chatWindows.map((conversationId) => (
                    <ChatWindow
                        key={conversationId}
                        conversationId={conversationId}
                        currentUserId={currentUserId}
                    />
                ))}
            </div>
        </div>
    )
}