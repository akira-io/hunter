import React, { useState, useEffect, useRef } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { ArrowLeft, Send, User as UserIcon } from 'lucide-react';
import { useChat } from '@/hooks/useChat';

interface User {
    id: number;
    name: string;
    avatar_url?: string;
}

interface Message {
    id: number;
    content: string;
    type: 'text' | 'image' | 'file';
    metadata?: any;
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

    const { sendMessage, sending } = useChat(currentUser.id);

    // Get the other participant (for direct conversations)
    const otherParticipant = conversation?.participants.find(p => p.id !== currentUser.id);

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
        scrollToBottom();
    }, [conversation?.messages]);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    const handleSendMessage = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!newMessage.trim() || sending) return;

        try {
            const message = await sendMessage(conversationId, newMessage.trim());
            setNewMessage('');

            // Add message to local state immediately for better UX
            setConversation(prev => {
                if (!prev) return prev;
                return {
                    ...prev,
                    messages: [...(prev.messages || []), message]
                };
            });
        } catch (error) {
            console.error('Failed to send message:', error);
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
        <div className="min-h-screen bg-zinc-50 dark:bg-zinc-900 flex flex-col">
            <Head title={`Chat - ${getConversationTitle()}`} />

            {/* Header */}
            <div className="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 px-4 py-3 flex items-center gap-3 sticky top-0 z-10">
                <button
                    onClick={goBack}
                    className="p-2 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-lg transition-colors"
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
                            />
                        ) : (
                            <div className="size-10 rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-600 flex items-center justify-center">
                                <UserIcon size={20} className="text-zinc-600 dark:text-zinc-300" />
                            </div>
                        )}
                    </div>

                    <div>
                        <h1 className="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                            {getConversationTitle()}
                        </h1>
                        <p className="text-sm text-zinc-500 dark:text-zinc-400">
                            {conversation?.type === 'direct' ? 'Direct message' : 'Group chat'}
                        </p>
                    </div>
                </div>
            </div>

            {/* Messages */}
            <div className="flex-1 overflow-y-auto p-4 space-y-4">
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
                                className={`flex ${isOwn ? 'justify-end' : 'justify-start'}`}
                            >
                                <div className={`flex gap-3 max-w-[85%] ${isOwn ? 'flex-row-reverse' : ''}`}>
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
                                            className={`px-4 py-3 rounded-2xl text-sm leading-relaxed ${
                                                isOwn
                                                    ? 'bg-gradient-to-r from-purple-500 to-purple-700 text-white'
                                                    : 'bg-white text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 border border-zinc-200 dark:border-zinc-700'
                                            }`}
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

            {/* Message Input */}
            <div className="bg-white dark:bg-zinc-800 border-t border-zinc-200 dark:border-zinc-700 p-4">
                <form onSubmit={handleSendMessage} className="flex gap-3">
                    <input
                        type="text"
                        value={newMessage}
                        onChange={(e) => setNewMessage(e.target.value)}
                        placeholder="Type a message..."
                        className="flex-1 px-4 py-3 border border-zinc-200 dark:border-zinc-600 rounded-2xl bg-zinc-50 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder-zinc-500 dark:placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent transition-all"
                        disabled={sending}
                    />
                    <button
                        type="submit"
                        disabled={!newMessage.trim() || sending}
                        className="px-4 py-3 bg-gradient-to-r from-purple-500 to-purple-700 text-white rounded-2xl hover:from-purple-600 hover:to-purple-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl"
                    >
                        <Send size={18} />
                    </button>
                </form>
            </div>
        </div>
    );
}