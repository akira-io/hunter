import { useChatContext } from '@/contexts/ChatContext';
import { useChat } from '@/hooks/useChat';
import { shouldUseMobileChat } from '@/hooks/useDeviceDetection';
import chat from '@/routes/chat';
import { router } from '@inertiajs/react';
import { MessageCircle, User as UserIcon } from 'lucide-react';
import React from 'react';

interface User {
    id: number;
    name: string;
    avatar_url?: string;
}

interface Conversation {
    id: number;
    title: string;
    type: 'direct' | 'group';
    participants: User[];
    last_message?: {
        id: number;
        content: string;
        created_at: string;
        user: User;
    };
    last_message_at?: string;
    unread_count: number;
    other_participant?: User;
}

interface ConversationsListProps {
    currentUserId?: number;
}

export const ConversationsList: React.FC<ConversationsListProps> = ({ currentUserId }) => {
    const { conversations, loading } = useChat(currentUserId);
    const { openChatWindow } = useChatContext();

    const handleConversationClick = (conversationId: number) => {
        if (shouldUseMobileChat()) {
            router.visit(chat.show.url(conversationId));
        } else {
            openChatWindow(conversationId);
        }
    };

    const formatTime = (dateString?: string) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        const now = new Date();
        const diffTime = Math.abs(now.getTime() - date.getTime());
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays === 0) {
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        } else if (diffDays === 1) {
            return 'Yesterday';
        } else if (diffDays < 7) {
            return date.toLocaleDateString([], { weekday: 'short' });
        } else {
            return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
        }
    };

    const getConversationTitle = (conversation: Conversation) => {
        if (conversation.title) {
            return conversation.title;
        }

        return conversation.other_participant?.name || 'Unknown User';
    };

    const getConversationAvatar = (conversation: Conversation) => {
        if (conversation.type === 'group') {
            return (
                <div className="flex h-12 w-12 items-center justify-center rounded-full bg-blue-500">
                    <MessageCircle size={20} className="text-white" />
                </div>
            );
        }

        const otherParticipant = conversation.other_participant;

        if (otherParticipant?.avatar_url) {
            return (
                <div className="relative h-12 w-12">
                    <img
                        src={otherParticipant.avatar_url}
                        alt={otherParticipant.name}
                        className="h-12 w-12 rounded-full object-cover"
                        onError={(e) => {
                            e.currentTarget.style.display = 'none';
                            const fallback = e.currentTarget.parentElement?.querySelector('.avatar-fallback') as HTMLElement;
                            if (fallback) {
                                fallback.style.display = 'flex';
                            }
                        }}
                    />
                    <div
                        className="avatar-fallback absolute inset-0 flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-600"
                        style={{ display: 'none' }}
                    >
                        <UserIcon size={20} className="text-zinc-600 dark:text-zinc-300" />
                    </div>
                </div>
            );
        }

        return (
            <div className="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-600">
                <UserIcon size={20} className="text-zinc-600 dark:text-zinc-300" />
            </div>
        );
    };

    if (loading) {
        return (
            <div className="p-4">
                <div className="animate-pulse space-y-4">
                    {[...Array(5)].map((_, i) => (
                        <div key={i} className="flex items-center gap-3">
                            <div className="h-12 w-12 rounded-full bg-gray-200"></div>
                            <div className="flex-1">
                                <div className="mb-2 h-4 w-3/4 rounded bg-gray-200"></div>
                                <div className="h-3 w-1/2 rounded bg-gray-200"></div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        );
    }

    return (
        <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
            <div className="border-b border-gray-200 p-4">
                <h2 className="text-lg font-semibold text-gray-900">Mensagens</h2>
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
                                className="flex cursor-pointer items-center gap-3 border-b border-gray-100 p-3 last:border-b-0 hover:bg-gray-50"
                                onClick={() => handleConversationClick(conversation.id)}
                            >
                                <div className="relative flex-shrink-0">
                                    {getConversationAvatar(conversation)}
                                    {conversation.unread_count > 0 && (
                                        <div className="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white">
                                            {conversation.unread_count > 99 ? '99+' : conversation.unread_count}
                                        </div>
                                    )}
                                </div>
                                <div className="min-w-0 flex-1">
                                    <div className="flex items-center justify-between">
                                        <h3 className="truncate text-sm font-medium text-gray-900">{getConversationTitle(conversation)}</h3>
                                        <span className="text-xs text-gray-500">{formatTime(conversation.last_message_at)}</span>
                                    </div>
                                    {conversation.last_message && (
                                        <p className="mt-1 truncate text-sm text-gray-600">
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
    );
};
