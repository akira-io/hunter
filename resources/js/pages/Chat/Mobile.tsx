import { useChat } from '@/hooks/useChat';
import { usePresence } from '@/hooks/usePresence';
import { usePresenceManager } from '@/hooks/usePresenceManager';
import { useFollowedHunters } from '@/stores/followedHuntersStore';
import { useOnlineUsers } from '@/stores/onlineUsersStore';
import { Head } from '@inertiajs/react';
import { useEcho } from '@laravel/echo-react';
import { ArrowLeft, Send, User as UserIcon } from 'lucide-react';
import React, { useEffect, useRef, useState } from 'react';

interface User {
    id: number;
    name: string;
    avatar_url?: string;
}

interface Message {
    id: number;
    content: string;
    type: 'text' | 'image' | 'file';
    metadata?: Record<string, unknown>;
    created_at: string;
    user: User;
}

interface Conversation {
    id: number;
    title: string;
    type: 'direct' | 'group';
    participants: User[];
    messages?: Message[];
}

interface MobileChatProps {
    conversationId: number;
    currentUser: User;
}

export default function MobileChat({ conversationId, currentUser }: MobileChatProps) {
    const [conversation, setConversation] = useState<Conversation | null>(null);
    const [newMessage, setNewMessage] = useState('');
    const [loading, setLoading] = useState(true);
    const messagesEndRef = useRef<HTMLDivElement>(null);
    const textareaRef = useRef<HTMLTextAreaElement>(null);

    const { sendMessage, sending, markMessagesAsRead } = useChat(currentUser.id);

    // Add presence management to keep user online while in mobile chat
    usePresence({ userId: currentUser.id });
    usePresenceManager({ currentUserId: currentUser.id });

    // WebSocket connection for real-time message updates
    const conversationEcho = useEcho<{ message: Message }>(
        conversationId ? `conversation.${conversationId}` : '',
        undefined,
        undefined,
        [],
        'private',
    );

    const otherParticipant = conversation?.participants.find((p) => p.id !== currentUser.id);

    const onlineUsers = useOnlineUsers();
    const followedHunters = useFollowedHunters();

    const isOtherUserOnline = otherParticipant
        ? onlineUsers.some((user) => user.id === otherParticipant.id) ||
          followedHunters.some((hunter) => hunter.id === otherParticipant.id && hunter.is_online)
        : false;

    useEffect(() => {
        const fetchConversation = async () => {
            try {
                setLoading(true);
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch(`/conversations/${conversationId}`, {
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (response.ok) {
                    const data = await response.json();
                    setConversation(data);
                    // Mark messages as read when conversation is opened
                    markMessagesAsRead(conversationId);
                }
            } catch (error) {
                console.error('Failed to load conversation:', error);
            } finally {
                setLoading(false);
            }
        };

        fetchConversation();
    }, [conversationId]); // eslint-disable-line react-hooks/exhaustive-deps

    useEffect(() => {
        const channel = conversationEcho.channel();
        if (!channel || !conversationId) {
            return;
        }

        const messageHandler = (event: { message: Message }) => {
            setConversation((prev) => {
                if (!prev) return prev;

                const messageExists = prev.messages?.some((msg) => msg.id === event.message.id);
                if (messageExists) {
                    return prev;
                }

                // Mark new messages as read since the conversation is active
                if (event.message.user.id !== currentUser.id) {
                    markMessagesAsRead(conversationId, [event.message.id]);
                }

                return {
                    ...prev,
                    messages: [...(prev.messages || []), event.message],
                };
            });
        };

        channel.listen('.message.sent', messageHandler);

        return () => {};
    }, [conversationEcho, conversationId]); // eslint-disable-line react-hooks/exhaustive-deps

    useEffect(() => {
        scrollToBottom();
    }, [conversation?.messages]);

    // Mark messages as read when component unmounts (user leaves chat)
    useEffect(() => {
        return () => {
            if (conversationId) {
                markMessagesAsRead(conversationId);
            }
        };
    }, [conversationId, markMessagesAsRead]);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    const resetTextareaHeight = () => {
        if (textareaRef.current) {
            textareaRef.current.style.height = 'auto';
            textareaRef.current.style.height = '44px'; // Reset to minimum height
        }
    };

    const handleSendMessage = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!newMessage.trim() || sending) return;

        try {
            await sendMessage(conversationId, newMessage.trim());
            setNewMessage('');

            // Reset textarea height to normal
            resetTextareaHeight();

            setTimeout(() => scrollToBottom(), 100);
        } catch (error) {
            console.error('Failed to send message:', error);
            // Could add a toast notification here for better UX
        }
    };

    const getConversationTitle = () => {
        if (!conversation) return 'Chat';

        if (conversation.title) {
            return conversation.title;
        }

        return otherParticipant?.name || 'Unknown User';
    };

    const formatTime = (dateString: string) => {
        const date = new Date(dateString);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    };

    const goBack = () => {
        window.history.back();
    };

    if (loading) {
        return (
            <div className="flex min-h-screen items-center justify-center bg-zinc-50 dark:bg-zinc-900">
                <Head title="Chat - Loading..." />
                <div className="h-12 w-12 animate-spin rounded-full border-b-2 border-purple-500"></div>
            </div>
        );
    }

    return (
        <div className="mobile-chat-container flex h-screen flex-col bg-zinc-50 dark:bg-zinc-900">
            <Head title={`Chat - ${getConversationTitle()}`} />
            {/* Mobile viewport meta for proper rendering */}
            <Head>
                <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
                <meta name="mobile-web-app-capable" content="yes" />
                <meta name="apple-mobile-web-app-capable" content="yes" />
                <meta name="apple-mobile-web-app-status-bar-style" content="default" />
            </Head>
            {/* Header - Fixed at top */}
            <div className="z-20 flex flex-shrink-0 items-center gap-3 border-b border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                <button
                    onClick={goBack}
                    className="rounded-lg p-2 transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-700"
                    data-testid="mobile-chat-back-button"
                >
                    <ArrowLeft size={20} className="text-zinc-600 dark:text-zinc-400" />
                </button>

                <div className="flex flex-1 items-center gap-3">
                    {/* Avatar */}
                    <div className="relative">
                        {otherParticipant?.avatar_url ? (
                            <img
                                src={otherParticipant.avatar_url}
                                alt={otherParticipant.name}
                                className="size-10 rounded-full object-cover"
                                data-testid="conversation-avatar"
                            />
                        ) : (
                            <div
                                className="flex size-10 items-center justify-center rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-600"
                                data-testid="conversation-avatar"
                            >
                                <UserIcon size={20} className="text-zinc-600 dark:text-zinc-300" />
                            </div>
                        )}
                        {/* Status indicator for direct conversations */}
                        {conversation?.type === 'direct' && otherParticipant && (
                            <div
                                className={`absolute -right-0.5 -bottom-0.5 size-3 rounded-full border-2 border-white dark:border-zinc-800 ${
                                    isOtherUserOnline ? 'bg-emerald-400' : 'bg-zinc-400'
                                }`}
                            />
                        )}
                    </div>
                    <div className="flex-1">
                        <h1 className="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{getConversationTitle()}</h1>
                        <div className="flex items-center gap-2">
                            <p className="text-sm text-zinc-500 dark:text-zinc-400">
                                {conversation?.type === 'direct' ? 'Conversa direta' : 'Chat em grupo'}
                            </p>
                            {conversation?.type === 'direct' && otherParticipant && (
                                <>
                                    <span className="text-xs text-zinc-400">•</span>
                                    <span
                                        className={`text-xs font-medium ${
                                            isOtherUserOnline ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-500 dark:text-zinc-400'
                                        }`}
                                    >
                                        {isOtherUserOnline ? 'Online' : 'Offline'}
                                    </span>
                                </>
                            )}
                        </div>
                    </div>
                </div>
            </div>
            {/* Messages - Scrollable area between fixed header and input */}
            <div className="min-h-0 flex-1 space-y-4 overflow-y-auto p-4">
                {conversation?.messages?.length === 0 ? (
                    <div className="flex h-full flex-col items-center justify-center text-center">
                        <div className="mb-4 grid size-16 place-items-center rounded-full bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-800 dark:to-zinc-700">
                            <UserIcon size={24} className="text-zinc-500 dark:text-zinc-400" />
                        </div>
                        <p className="mb-2 text-lg font-medium text-zinc-900 dark:text-zinc-100">Start the conversation!</p>
                        <p className="text-sm text-zinc-500 dark:text-zinc-400">Send a message to get started</p>
                    </div>
                ) : (
                    conversation?.messages?.map((message) => {
                        const isOwn = message.user.id === currentUser.id;

                        return (
                            <div key={message.id} className={`flex ${isOwn ? 'justify-end' : 'justify-start'} w-full`} data-testid="chat-message">
                                <div className={`flex max-w-[85%] min-w-0 gap-3 ${isOwn ? 'flex-row-reverse' : ''}`}>
                                    <div className="flex-shrink-0">
                                        {message.user.avatar_url ? (
                                            <img src={message.user.avatar_url} alt={message.user.name} className="size-8 rounded-full object-cover" />
                                        ) : (
                                            <div className="grid size-8 place-items-center rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-600">
                                                <UserIcon size={14} className="text-zinc-600 dark:text-zinc-300" />
                                            </div>
                                        )}
                                    </div>
                                    <div className={`flex flex-col ${isOwn ? 'items-end' : 'items-start'}`}>
                                        <div
                                            className={`mobile-chat-message rounded-2xl px-4 py-3 text-sm leading-relaxed break-words whitespace-pre-wrap ${
                                                isOwn
                                                    ? 'bg-gradient-to-r from-purple-500 to-purple-700 text-white'
                                                    : 'border border-zinc-200 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100'
                                            }`}
                                            style={{
                                                wordBreak: 'break-word',
                                                overflowWrap: 'anywhere',
                                                hyphens: 'auto',
                                            }}
                                        >
                                            {message.content}
                                        </div>
                                        <div className={`mt-1 text-xs text-zinc-500 dark:text-zinc-400 ${isOwn ? 'mr-1' : 'ml-1'}`}>
                                            {formatTime(message.created_at)}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        );
                    })
                )}
                <div ref={messagesEndRef} />
            </div>
            {/* Message Input - Fixed at bottom */}
            <div className="safe-area-inset-bottom mobile-input-container flex-shrink-0 border-t border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <form onSubmit={handleSendMessage} className="flex items-end gap-3">
                    <textarea
                        ref={textareaRef}
                        value={newMessage}
                        onChange={(e) => setNewMessage(e.target.value)}
                        onKeyDown={(e) => {
                            if (e.key === 'Enter' && !e.shiftKey) {
                                e.preventDefault();
                                handleSendMessage(e);
                            }
                        }}
                        placeholder="Digite uma mensagem..."
                        className="mobile-message-input flex-1 resize-none rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder-zinc-500 transition-all focus:border-transparent focus:ring-2 focus:ring-purple-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100 dark:placeholder-zinc-400 dark:focus:ring-purple-400"
                        disabled={sending}
                        autoComplete="off"
                        data-testid="mobile-message-input"
                        rows={1}
                        style={{
                            minHeight: '44px',
                            maxHeight: '120px',
                            height: 'auto',
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
                        className="rounded-2xl bg-gradient-to-r from-purple-500 to-purple-700 px-4 py-3 text-white shadow-lg transition-all duration-200 hover:from-purple-600 hover:to-purple-800 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50"
                        data-testid="mobile-send-button"
                    >
                        <Send size={18} />
                    </button>
                </form>
            </div>
        </div>
    );
}
