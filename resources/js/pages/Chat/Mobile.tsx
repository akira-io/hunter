import React, { useEffect, useRef, useState } from 'react';
import { Head } from '@inertiajs/react';
import { ArrowLeft, Send, User as UserIcon } from 'lucide-react';
import { useChat } from '@/hooks/useChat';
import { usePresence } from '@/hooks/usePresence';
import { usePresenceManager } from '@/hooks/usePresenceManager';
import { useEcho } from '@laravel/echo-react';
import { useOnlineUsers } from '@/stores/onlineUsersStore';
import { useFollowedHunters } from '@/stores/followedHuntersStore';

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

    const { sendMessage, sending } = useChat(currentUser.id);

    // Add presence management to keep user online while in mobile chat
    usePresence({ userId: currentUser.id });
    usePresenceManager({ currentUserId: currentUser.id });

    // WebSocket connection for real-time message updates
    const conversationEcho = useEcho<{ message: Message }>(
        conversationId ? `conversation.${conversationId}` : '',
        undefined,
        undefined,
        [],
        'private'
    );


    const otherParticipant = conversation?.participants.find(p => p.id !== currentUser.id);

    const onlineUsers = useOnlineUsers();
    const followedHunters = useFollowedHunters();

    const isOtherUserOnline = otherParticipant ?
        onlineUsers.some(user => user.id === otherParticipant.id) ||
        followedHunters.some(hunter => hunter.id === otherParticipant.id && hunter.is_online)
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
                }
            } catch (error) {
                console.error('Failed to load conversation:', error);
            } finally {
                setLoading(false);
            }
        };

        fetchConversation();
    }, [conversationId]);


    useEffect(() => {
        const channel = conversationEcho.channel();
        if (!channel || !conversationId) {
            return;
        }

        const messageHandler = (event: { message: Message }) => {
            setConversation(prev => {
                if (!prev) return prev;

                const messageExists = prev.messages?.some(msg => msg.id === event.message.id);
                if (messageExists) {
                    return prev;
                }

                return {
                    ...prev,
                    messages: [...(prev.messages || []), event.message]
                };
            });
        };

        channel.listen('.message.sent', messageHandler);

        return () => {
        };
    }, [conversationEcho, conversationId]);

    useEffect(() => {
        scrollToBottom();
    }, [conversation?.messages]);

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
            <div className="min-h-screen bg-zinc-50 dark:bg-zinc-900 flex items-center justify-center">
                <Head title="Chat - Loading..." />
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-500"></div>
            </div>
        );
    }

    return (
        <div className='h-screen bg-zinc-50 dark:bg-zinc-900 flex flex-col mobile-chat-container'>
            <Head title={`Chat - ${getConversationTitle()}`} />
            {/* Mobile viewport meta for proper rendering */}
            <Head>
                <meta name='viewport'
                      content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no' />
                <meta name='mobile-web-app-capable' content='yes' />
                <meta name='apple-mobile-web-app-capable' content='yes' />
                <meta name='apple-mobile-web-app-status-bar-style' content='default' />
            </Head>
            {/* Header - Fixed at top */}
            <div className='bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 px-4 py-3 flex items-center gap-3 flex-shrink-0 z-20'>
                <button
                    onClick={goBack}
                    className="p-2 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-lg transition-colors"
                    data-testid='mobile-chat-back-button'
                >
                    <ArrowLeft size={20} className="text-zinc-600 dark:text-zinc-400" />
                </button>

                <div className="flex items-center gap-3 flex-1">
                    {/* Avatar */}
                    <div className="relative">
                        {otherParticipant?.avatar_url ? (
                            <img
                                src={otherParticipant.avatar_url}
                                alt={otherParticipant.name}
                                className="size-10 rounded-full object-cover"
                                data-testid='conversation-avatar'
                            />
                        ) : (
                            <div
                                className='size-10 rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-600 flex items-center justify-center'
                                data-testid='conversation-avatar'
                            >
                                <UserIcon size={20} className="text-zinc-600 dark:text-zinc-300" />
                            </div>
                        )}
                        {/* Status indicator for direct conversations */}
                        {conversation?.type === 'direct' && otherParticipant && (
                            <div className={`absolute -bottom-0.5 -right-0.5 size-3 rounded-full border-2 border-white dark:border-zinc-800 ${
                                isOtherUserOnline
                                    ? 'bg-emerald-400'
                                    : 'bg-zinc-400'
                            }`} />
                        )}
                    </div>
                    <div className='flex-1'>
                        <h1 className="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                            {getConversationTitle()}
                        </h1>
                        <div className='flex items-center gap-2'>
                            <p className='text-sm text-zinc-500 dark:text-zinc-400'>
                                {conversation?.type === 'direct' ? 'Conversa direta' : 'Chat em grupo'}
                            </p>
                            {conversation?.type === 'direct' && otherParticipant && (
                                <>
                                    <span className='text-xs text-zinc-400'>•</span>
                                    <span className={`text-xs font-medium ${
                                        isOtherUserOnline
                                            ? 'text-emerald-600 dark:text-emerald-400'
                                            : 'text-zinc-500 dark:text-zinc-400'
                                    }`}>
                                        {isOtherUserOnline ? 'Online' : 'Offline'}
                                    </span>
                                </>
                            )}
                        </div>
                    </div>
                </div>
            </div>
            {/* Messages - Scrollable area between fixed header and input */}
            <div className='flex-1 overflow-y-auto p-4 space-y-4 min-h-0'>
                {conversation?.messages?.length === 0 ? (
                    <div className="flex flex-col items-center justify-center h-full text-center">
                        <div className="size-16 rounded-full bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-800 dark:to-zinc-700 grid place-items-center mb-4">
                            <UserIcon size={24} className="text-zinc-500 dark:text-zinc-400" />
                        </div>
                        <p className="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">
                            Start the conversation!
                        </p>
                        <p className="text-sm text-zinc-500 dark:text-zinc-400">
                            Send a message to get started
                        </p>
                    </div>
                ) : (
                    conversation?.messages?.map((message) => {
                        const isOwn = message.user.id === currentUser.id;

                        return (
                            <div
                                key={message.id}
                                className={`flex ${isOwn ? 'justify-end' : 'justify-start'} w-full`}
                                data-testid='chat-message'
                            >
                                <div className={`flex gap-3 max-w-[85%] min-w-0 ${isOwn ? 'flex-row-reverse' : ''}`}>
                                    <div className="flex-shrink-0">
                                        {message.user.avatar_url ? (
                                            <img
                                                src={message.user.avatar_url}
                                                alt={message.user.name}
                                                className="size-8 rounded-full object-cover"
                                            />
                                        ) : (
                                            <div className="size-8 rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-600 grid place-items-center">
                                                <UserIcon size={14} className="text-zinc-600 dark:text-zinc-300" />
                                            </div>
                                        )}
                                    </div>
                                    <div className={`flex flex-col ${isOwn ? 'items-end' : 'items-start'}`}>
                                        <div
                                            className={`px-4 py-3 rounded-2xl text-sm leading-relaxed mobile-chat-message break-words whitespace-pre-wrap ${
                                                isOwn
                                                    ? 'bg-gradient-to-r from-purple-500 to-purple-700 text-white'
                                                    : 'bg-white text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 border border-zinc-200 dark:border-zinc-700'
                                            }`}
                                            style={{
                                                wordBreak: 'break-word',
                                                overflowWrap: 'anywhere',
                                                hyphens: 'auto'
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
                        );
                    })
                )}
                <div ref={messagesEndRef} />
            </div>
            {/* Message Input - Fixed at bottom */}
            <div className='bg-white dark:bg-zinc-800 border-t border-zinc-200 dark:border-zinc-700 p-4 flex-shrink-0 safe-area-inset-bottom z-20'>
                <form onSubmit={handleSendMessage} className='flex gap-3 items-end'>
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
                        placeholder='Digite uma mensagem...'
                        className='flex-1 px-4 py-3 border border-zinc-200 dark:border-zinc-600 rounded-2xl bg-zinc-50 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder-zinc-500 dark:placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent transition-all mobile-message-input resize-none'
                        disabled={sending}
                        autoComplete='off'
                        data-testid='mobile-message-input'
                        rows={1}
                        style={{
                            minHeight: '44px',
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
                        className="px-4 py-3 bg-gradient-to-r from-purple-500 to-purple-700 text-white rounded-2xl hover:from-purple-600 hover:to-purple-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl"
                        data-testid='mobile-send-button'
                    >
                        <Send size={18} />
                    </button>
                </form>
            </div>
        </div>
    );
}