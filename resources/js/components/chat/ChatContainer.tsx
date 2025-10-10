import { UserAvatar } from '@/components/UserAvatar';
import { useChatContext } from '@/contexts/ChatContext';
import { shouldUseMobileChat } from '@/hooks/use-device-detection';
import chat from '@/routes/chat';
import { router } from '@inertiajs/react';
import { X } from 'lucide-react';
import React from 'react';
import { ChatWindow } from './ChatWindow';

interface ChatContainerProps {
    currentUserId?: number;
}

export const ChatContainer: React.FC<ChatContainerProps> = ({
    currentUserId,
}) => {
    const {
        chatWindows,
        backgroundWindows,
        switchToWindow,
        closeBackgroundWindow,
        conversations,
    } = useChatContext();

    const getConversationTitle = (conversationId: number) => {
        const conv = conversations.find((c) => c.id === conversationId);
        if (!conv) return `Chat ${conversationId}`;

        if (conv.title) return conv.title;

        const otherParticipant = conv.participants.find(
            (p) => p.id !== currentUserId,
        );
        return otherParticipant?.name || 'Unknown User';
    };

    const getUnreadCount = (conversationId: number) => {
        const conv = conversations.find((c) => c.id === conversationId);
        return conv?.unread_count || 0;
    };

    const getOtherParticipant = (conversationId: number) => {
        const conv = conversations.find((c) => c.id === conversationId);
        if (!conv) return null;
        return conv.participants.find((p) => p.id !== currentUserId);
    };

    return (
        <div className="fixed right-24 bottom-6 z-40 flex items-end gap-2">
            {/* Background chat tabs - stacked vertically on the left */}
            {backgroundWindows.length > 0 && (
                <div className="flex flex-col items-end gap-1">
                    {backgroundWindows.map((conversationId) => {
                        const unreadCount = getUnreadCount(conversationId);
                        const otherParticipant =
                            getOtherParticipant(conversationId);
                        return (
                            <div
                                key={conversationId}
                                className="group relative w-48 rounded-lg border border-zinc-600/50 bg-zinc-800/90 text-xs font-medium text-white shadow-lg backdrop-blur-sm transition-all duration-200 hover:scale-105 hover:bg-zinc-700/90"
                            >
                                <button
                                    onClick={() => {
                                        if (shouldUseMobileChat()) {
                                            router.visit(
                                                chat.show.url(conversationId),
                                            );
                                        } else {
                                            switchToWindow(conversationId);
                                        }
                                    }}
                                    className="flex w-full items-center gap-2 px-3 py-2 pr-8 text-left"
                                >
                                    {/* Avatar */}
                                    <UserAvatar
                                        avatarUrl={otherParticipant?.avatar_url}
                                        userName={
                                            otherParticipant?.name || 'Unknown'
                                        }
                                        className="size-6 ring-1 ring-white/20"
                                        fallbackClassName="text-[10px]"
                                    />

                                    <span className="flex-1 truncate">
                                        {getConversationTitle(conversationId)}
                                    </span>
                                    {unreadCount > 0 && (
                                        <div className="flex h-4 min-w-[16px] shrink-0 items-center justify-center rounded-full bg-red-500 px-1 text-xs font-bold text-white">
                                            {unreadCount > 9
                                                ? '9+'
                                                : unreadCount}
                                        </div>
                                    )}
                                </button>
                                <button
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        closeBackgroundWindow(conversationId);
                                    }}
                                    className="absolute top-1/2 right-1 -translate-y-1/2 rounded-full p-1 opacity-60 transition-colors hover:bg-zinc-600/50 hover:opacity-100"
                                >
                                    <X size={10} />
                                </button>
                            </div>
                        );
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
    );
};
