import { useChatContext } from '@/contexts/ChatContext';
import { useTimeFormatting } from '@/hooks/use-time-formatting';
import { useFollowedHunters } from '@/stores/followedHuntersStore';
import { useOnlineUsers } from '@/stores/onlineUsersStore';
import { useEcho } from '@laravel/echo-react';
import { Minus, Send, User as UserIcon, X } from 'lucide-react';
import React, { useEffect, useRef, useState } from 'react';

interface Message {
    id: number;
    content: string;
    type: 'text' | 'image' | 'file';
    metadata?: Record<string, unknown> | null;
    created_at: string;
    user: {
        id: number;
        name: string;
        avatar_url?: string;
    };
}

interface Conversation {
    id: number;
    title: string;
    type: 'direct' | 'group';
    participants: Array<{
        id: number;
        name: string;
        avatar_url?: string;
    }>;
    messages?: Message[];
    unread_count?: number;
}

interface ChatWindowProps {
    conversationId: number;
    currentUserId?: number;
}

export const ChatWindow: React.FC<ChatWindowProps> = ({ conversationId, currentUserId }) => {
    const [newMessage, setNewMessage] = useState('');
    const [conversation, setConversation] = useState<Conversation | null>(null);
    const [localLoading, setLocalLoading] = useState(true);
    const [hasInitiallyScrolled, setHasInitiallyScrolled] = useState(false);
    const messagesEndRef = useRef<HTMLDivElement>(null);
    const messagesContainerRef = useRef<HTMLDivElement>(null);
    const textareaRef = useRef<HTMLTextAreaElement>(null);
    const prevMessageCountRef = useRef<number>(0);

    const { closeChatWindow, minimizedWindows, toggleMinimize, sendMessage, sending, markMessagesAsRead, conversations } = useChatContext();
    const { formatTime } = useTimeFormatting();

    // Get conversation from centralized state for real-time updates (especially unread_count)
    const centralConversation = conversations.find((c) => c.id === conversationId);

    // Create a dedicated WebSocket connection for this specific conversation
    const conversationEcho = useEcho<{ message: Message }>(
        conversationId ? `conversation.${conversationId}` : '',
        undefined,
        undefined,
        [],
        'private',
    );
    const onlineUsers = useOnlineUsers();
    const followedHunters = useFollowedHunters();

    const isMinimized = minimizedWindows.has(conversationId);

    // Auto-focus input when chat window is opened or unminimized
    useEffect(() => {
        if (!isMinimized && !localLoading && textareaRef.current) {
            // Small delay to ensure DOM is ready
            requestAnimationFrame(() => {
                textareaRef.current?.focus();
            });
        }
    }, [isMinimized, localLoading]);

    // Get the other participant (for direct conversations)
    const otherParticipant = conversation?.participants.find((p) => p.id !== currentUserId);

    // Check if the other participant is online
    const isOtherUserOnline = otherParticipant
        ? onlineUsers.some((user) => user.id === otherParticipant.id) ||
          followedHunters.some((hunter) => hunter.id === otherParticipant.id && hunter.is_online)
        : false;

    // Load conversation locally for this specific ChatWindow
    useEffect(() => {
        const fetchConversation = async () => {
            try {
                setLocalLoading(true);
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

                    // Scroll to bottom when conversation first loads
                    if (data.messages && data.messages.length > 0) {
                        setTimeout(() => {
                            scrollToBottom();
                            setHasInitiallyScrolled(true);
                        }, 100);
                    }

                    // Mark messages as read when conversation loads and reset unread count
                    await markMessagesAsRead(conversationId);
                }
            } catch (error) {
                console.error('Failed to load conversation:', error);
            } finally {
                setLocalLoading(false);
            }
        };

        fetchConversation();
    }, [conversationId, markMessagesAsRead]);

    // Subscribe to WebSocket for this specific conversation
    useEffect(() => {
        const channel = conversationEcho.channel();
        if (!channel) {
            return;
        }

        const messageHandler = (event: { message: Message }) => {
            setConversation((prev) => {
                if (!prev) return prev;

                // Check if message already exists (avoid duplicates)
                const messageExists = prev.messages?.some((msg) => msg.id === event.message.id);
                if (messageExists) {
                    return prev;
                }

                return {
                    ...prev,
                    messages: [...(prev.messages || []), event.message],
                };
            });
        };

        channel.listen('.message.sent', messageHandler);

        return () => {
            // Laravel Echo React handles cleanup automatically
        };
    }, [conversationEcho, conversationId]);

    // Check if user is at bottom of chat (within threshold)
    const isNearBottom = () => {
        const container = messagesContainerRef.current;
        if (!container) return true;

        const { scrollTop, scrollHeight, clientHeight } = container;
        const threshold = 100; // pixels from bottom
        return scrollHeight - scrollTop - clientHeight < threshold;
    };

    useEffect(() => {
        const currentMessageCount = conversation?.messages?.length || 0;
        const prevMessageCount = prevMessageCountRef.current;

        // Only apply smart scrolling rules if chat has been initially loaded
        if (hasInitiallyScrolled) {
            // Only scroll if:
            // 1. Messages were added (not just conversation loaded)
            // 2. Window is not minimized
            // 3. User is already near the bottom (not reading history)
            if (currentMessageCount > prevMessageCount && !isMinimized && isNearBottom()) {
                scrollToBottom();
            }
        }

        prevMessageCountRef.current = currentMessageCount;
    }, [conversation?.messages?.length, isMinimized, conversationId, hasInitiallyScrolled]);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    const handleSendMessage = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!newMessage.trim() || sending) return;

        try {
            await sendMessage(conversationId, newMessage.trim());
            setNewMessage('');

            // Reset textarea height and refocus after DOM updates
            requestAnimationFrame(() => {
                if (textareaRef.current) {
                    textareaRef.current.style.height = 'auto';
                    textareaRef.current.style.height = '40px';
                    textareaRef.current.focus();
                }
            });

            // Don't add message locally - let WebSocket handle it to avoid duplicates
            // Always scroll when user sends a message (intentional action)
            setTimeout(() => scrollToBottom(), 50);
        } catch (error) {
            console.error('Failed to send message:', error);
        }
    };

    const handleKeyDown = (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleSendMessage(e);
        }
    };

    const getConversationTitle = () => {
        if (!conversation) return 'Loading...';

        if (conversation.title) {
            return conversation.title;
        }

        // For direct conversations, show the other person's name
        const otherParticipant = conversation.participants.find((p) => p.id !== currentUserId);
        return otherParticipant?.name || 'Unknown User';
    };

    if (localLoading) {
        return (
            <div className="gradient bg-card w-80 rounded-2xl border border-zinc-200 shadow-2xl backdrop-blur-lg dark:border-zinc-700">
                <div className="flex items-center justify-between rounded-t-2xl border-b border-zinc-200 bg-zinc-100 px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    <span className="text-sm font-medium">Loading...</span>
                </div>
                <div className="flex h-80 items-center justify-center">
                    <div className="h-8 w-8 animate-spin rounded-full border-b-2 border-purple-500"></div>
                </div>
            </div>
        );
    }

    return (
        <div
            className={`gradient bg-card flex w-80 flex-col rounded-2xl border shadow-2xl backdrop-blur-lg ${isMinimized ? 'h-auto' : 'h-[26rem]'} relative ${
                isMinimized && centralConversation?.unread_count && centralConversation.unread_count > 0
                    ? 'border-red-300 shadow-red-100 dark:border-red-700 dark:shadow-red-900/20'
                    : 'border-zinc-200 dark:border-zinc-700'
            }`}
        >
            {/* Header */}
            <div
                className={`flex items-center justify-between bg-zinc-100 px-4 py-3 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 ${isMinimized ? 'rounded-2xl' : 'rounded-t-2xl border-b border-zinc-200 dark:border-zinc-700'} cursor-pointer transition-colors hover:bg-zinc-200 dark:hover:bg-zinc-700`}
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
                                    className="grid size-10 place-items-center rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 ring-2 ring-white dark:from-zinc-700 dark:to-zinc-600 dark:ring-zinc-800"
                                    style={{ display: 'none' }}
                                >
                                    <UserIcon size={20} className="text-zinc-600 dark:text-zinc-300" />
                                </div>
                            </>
                        ) : (
                            <div className="flex size-8 items-center justify-center rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-600">
                                <UserIcon size={16} className="text-zinc-600 dark:text-zinc-300" />
                            </div>
                        )}

                        {/* Status indicator */}
                        <div
                            className={`absolute -right-0.5 -bottom-0.5 size-3 rounded-full border-2 border-zinc-100 dark:border-zinc-800 ${
                                isOtherUserOnline ? 'animate-pulse bg-emerald-400' : 'bg-zinc-400'
                            }`}
                        />
                    </div>

                    <div className="flex flex-col">
                        <div className="flex items-center gap-2">
                            <span className="truncate text-sm font-medium">{getConversationTitle()}</span>
                        </div>
                        <span className="text-xs text-zinc-500 dark:text-zinc-400">{isOtherUserOnline ? 'Online' : 'Offline'}</span>
                    </div>
                </div>
                <div className="flex gap-1">
                    <button
                        onClick={(e) => {
                            e.stopPropagation();
                            toggleMinimize(conversationId);
                        }}
                        className="rounded-lg p-1.5 transition-colors hover:bg-white/20"
                    >
                        <Minus size={14} />
                    </button>
                    <button
                        onClick={(e) => {
                            e.stopPropagation();
                            closeChatWindow(conversationId);
                        }}
                        className="rounded-lg p-1.5 transition-colors hover:bg-white/20"
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
                        className="scrollbar-thin scrollbar-thumb-zinc-300 dark:scrollbar-thumb-zinc-600 scrollbar-track-transparent max-h-80 min-h-0 flex-1 space-y-4 overflow-y-auto p-4"
                    >
                        {conversation?.messages?.length === 0 ? (
                            <div className="flex h-32 flex-col items-center justify-center text-center">
                                <div className="mb-3 grid size-12 place-items-center rounded-full bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-800 dark:to-zinc-700">
                                    <UserIcon size={20} className="text-zinc-500 dark:text-zinc-400" />
                                </div>
                                <p className="text-sm text-zinc-500 dark:text-zinc-400">Inicie a conversa!</p>
                            </div>
                        ) : (
                            conversation?.messages?.map((message, index) => {
                                const isOwn = message.user.id === currentUserId;

                                return (
                                    <div
                                        key={`${message.id}-${index}-${message.created_at}`}
                                        className={`flex ${isOwn ? 'justify-end' : 'justify-start'}`}
                                    >
                                        <div className={`flex max-w-[85%] gap-3 ${isOwn ? 'flex-row-reverse' : ''}`}>
                                            <div className="flex-shrink-0">
                                                {message.user.avatar_url ? (
                                                    <img
                                                        src={message.user.avatar_url}
                                                        alt={message.user.name}
                                                        className="size-8 rounded-full object-cover ring-2 ring-white dark:ring-zinc-800"
                                                    />
                                                ) : (
                                                    <div className="grid size-8 place-items-center rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 ring-2 ring-white dark:from-zinc-700 dark:to-zinc-600 dark:ring-zinc-800">
                                                        <UserIcon size={14} className="text-zinc-600 dark:text-zinc-300" />
                                                    </div>
                                                )}
                                            </div>
                                            <div className={`flex flex-col ${isOwn ? 'items-end' : 'items-start'} min-w-0 flex-1`}>
                                                <div
                                                    className={`rounded-2xl px-4 py-2.5 text-sm leading-relaxed break-words whitespace-pre-wrap ${
                                                        isOwn
                                                            ? 'bg-gradient-to-r from-purple-500 to-purple-700 text-white shadow-md'
                                                            : 'bg-zinc-100 text-zinc-900 shadow-sm dark:bg-zinc-800 dark:text-zinc-100'
                                                    }`}
                                                    style={{
                                                        wordBreak: 'break-word',
                                                        overflowWrap: 'anywhere',
                                                        whiteSpace: 'pre-wrap',
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

                    {/* Message Input */}
                    <form
                        onSubmit={handleSendMessage}
                        className="rounded-b-2xl border-t border-zinc-200 bg-zinc-50/50 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800/50"
                    >
                        <div className="flex items-end gap-2">
                            <textarea
                                ref={textareaRef}
                                value={newMessage}
                                onChange={(e) => setNewMessage(e.target.value)}
                                onKeyDown={handleKeyDown}
                                placeholder="Digite uma mensagem..."
                                className="flex-1 resize-none rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-500 transition-all focus:border-purple-500 focus:ring-2 focus:ring-purple-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100 dark:placeholder-zinc-400 dark:focus:border-zinc-400 dark:focus:ring-zinc-400"
                                disabled={sending}
                                rows={1}
                                style={{
                                    minHeight: '40px',
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
                                className="rounded-xl bg-gradient-to-r from-purple-500 to-purple-700 px-3 py-2 text-white shadow-lg transition-all duration-200 hover:from-purple-600 hover:to-purple-800 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:from-purple-500 disabled:hover:to-purple-700"
                            >
                                <Send size={16} />
                            </button>
                        </div>
                    </form>
                </>
            )}
        </div>
    );
};
