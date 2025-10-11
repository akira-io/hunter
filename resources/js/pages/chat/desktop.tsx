import { UserAvatar } from '@/components/UserAvatar';
import { usePresence } from '@/hooks/use-presence';
import { useTimeFormatting } from '@/hooks/use-time-formatting';
import ChatLayout from '@/layouts/chat-layout';
import api from '@/lib/api';
import { useFollowedHunters } from '@/stores/followedHuntersStore';
import { useOnlineUsers } from '@/stores/onlineUsersStore';
import { type Conversation, type Message, type User } from '@/types/chat';
import { Head } from '@inertiajs/react';
import { useEcho } from '@laravel/echo-react';
import { ArrowLeft, Send, User as UserIcon } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

interface DesktopChatProps {
    conversationId: number;
    currentUser: User;
}

export default function DesktopChat({
    conversationId,
    currentUser,
}: DesktopChatProps) {
    const [conversation, setConversation] = useState<Conversation | null>(null);
    const [newMessage, setNewMessage] = useState('');
    const [loading, setLoading] = useState(true);
    const [sending] = useState(false);
    const messagesEndRef = useRef<HTMLDivElement>(null);
    const textareaRef = useRef<HTMLTextAreaElement>(null);

    usePresence({ userId: currentUser.id });
    const { formatTime } = useTimeFormatting();

    const conversationEcho = useEcho<{ message: Message }>(
        conversationId ? `conversation.${conversationId}` : '',
        undefined,
        undefined,
        [],
        'private',
    );

    const otherParticipant = conversation?.participants.find(
        (p) => p.id !== currentUser.id,
    );
    const onlineUsers = useOnlineUsers();
    const followedHunters = useFollowedHunters();

    const isOtherUserOnline = otherParticipant
        ? onlineUsers.some((user) => user.id === otherParticipant.id) ||
          followedHunters.some(
              (hunter) => hunter.id === otherParticipant.id && hunter.is_online,
          )
        : false;

    useEffect(() => {
        const fetchConversation = async () => {
            try {
                setLoading(true);
                const response = await api.get(
                    `/conversations/${conversationId}`,
                );

                if (response.data) {
                    setConversation(response.data);
                    // Mark messages as read
                    await markMessagesAsRead(conversationId);
                    // Scroll to bottom after loading conversation
                    setTimeout(() => scrollToBottom(), 100);
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
            setConversation((prev) => {
                if (!prev) return prev;

                const messageExists = prev.messages?.some(
                    (msg) => msg.id === event.message.id,
                );
                if (messageExists) {
                    return prev;
                }

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

    useEffect(() => {
        return () => {
            if (conversationId) {
                markMessagesAsRead(conversationId);
            }
        };
    }, [conversationId]);

    // Auto-focus input when conversation loads
    useEffect(() => {
        if (!loading && textareaRef.current) {
            textareaRef.current.focus();
        }
    }, [loading, conversationId]);

    const markMessagesAsRead = async (
        conversationId: number,
        messageIds?: number[],
    ) => {
        try {
            await api.post(`/conversations/${conversationId}/messages/read`, {
                message_ids: messageIds,
            });
        } catch (error) {
            console.error('Failed to mark messages as read:', error);
        }
    };

    const sendMessageToServer = async (
        conversationId: number,
        content: string,
    ) => {
        try {
            const response = await api.post('/messages', {
                conversation_id: conversationId,
                content,
                type: 'text',
            });
            return response.data;
        } catch (error) {
            console.error('Failed to send message:', error);
            throw error;
        }
    };

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    const handleSendMessage = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!newMessage.trim() || sending) return;

        const messageContent = newMessage.trim();

        // Store scroll position before clearing
        const textarea = textareaRef.current;

        // Clear input immediately while keeping focus
        setNewMessage('');

        // Keep textarea focused and reset height synchronously
        if (textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = '44px';
        }

        try {
            await sendMessageToServer(conversationId, messageContent);
            setTimeout(() => scrollToBottom(), 100);
        } catch (error) {
            console.error('Failed to send message:', error);
            setNewMessage(messageContent);
        }
    };

    const getConversationTitle = () => {
        if (!conversation) return 'Chat';
        if (conversation.title) return conversation.title;
        return otherParticipant?.name || 'Unknown User';
    };

    if (loading) {
        return (
            <ChatLayout
                title="Chat - Loading..."
                showSidebar={true}
                conversationId={conversationId}
            >
                <div className="flex h-full items-center justify-center">
                    <div className="size-12 animate-spin rounded-full border-b-2 border-purple-500"></div>
                </div>
            </ChatLayout>
        );
    }

    return (
        <ChatLayout
            title={`Chat - ${getConversationTitle()}`}
            showSidebar={true}
            conversationId={conversationId}
        >
            <Head title={`Chat - ${getConversationTitle()}`} />

            <div className="flex h-full flex-col">
                {/* Chat Header */}
                <div
                    className="fixed top-0 right-0 left-0 z-50 flex items-center gap-3 border-b border-zinc-200 bg-white px-4 py-3 md:left-80 md:px-6 md:py-4 dark:border-zinc-700 dark:bg-zinc-800"
                    style={{
                        paddingTop: 'max(0.75rem, env(safe-area-inset-top))',
                    }}
                >
                    {/* Mobile back button */}
                    <button
                        onClick={() => window.history.back()}
                        className="rounded-lg p-2 transition-colors hover:bg-zinc-100 md:hidden dark:hover:bg-zinc-700"
                        aria-label="Back to conversations"
                    >
                        <ArrowLeft
                            size={20}
                            className="text-zinc-600 dark:text-zinc-400"
                        />
                    </button>
                    <div className="relative">
                        {otherParticipant?.avatar_url ? (
                            <img
                                src={otherParticipant.avatar_url}
                                alt={otherParticipant.name}
                                className="size-10 rounded-full object-cover"
                            />
                        ) : (
                            <div className="flex size-10 items-center justify-center rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-600">
                                <UserIcon
                                    size={20}
                                    className="text-zinc-600 dark:text-zinc-300"
                                />
                            </div>
                        )}
                        {conversation?.type === 'direct' &&
                            otherParticipant && (
                                <div
                                    className={`absolute -right-0.5 -bottom-0.5 size-3 rounded-full border-2 border-white dark:border-zinc-800 ${
                                        isOtherUserOnline
                                            ? 'bg-emerald-400'
                                            : 'bg-zinc-400'
                                    }`}
                                />
                            )}
                    </div>
                    <div className="flex-1">
                        <h2 className="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                            {getConversationTitle()}
                        </h2>
                        <div className="flex items-center gap-2">
                            <p className="text-sm text-zinc-500 dark:text-zinc-400">
                                {conversation?.type === 'direct'
                                    ? 'Direct conversation'
                                    : 'Group chat'}
                            </p>
                            {conversation?.type === 'direct' &&
                                otherParticipant && (
                                    <>
                                        <span className="text-xs text-zinc-400">
                                            •
                                        </span>
                                        <span
                                            className={`text-xs font-medium ${
                                                isOtherUserOnline
                                                    ? 'text-emerald-600 dark:text-emerald-400'
                                                    : 'text-zinc-500 dark:text-zinc-400'
                                            }`}
                                        >
                                            {isOtherUserOnline
                                                ? 'Online'
                                                : 'Offline'}
                                        </span>
                                    </>
                                )}
                        </div>
                    </div>
                </div>

                {/* Messages Area */}
                <div
                    className="min-h-0 flex-1 space-y-4 overflow-y-auto p-4 md:p-6"
                    style={{
                        paddingTop:
                            'calc(env(safe-area-inset-top, 0px) + 64px)',
                    }}
                >
                    {conversation?.messages?.length === 0 ? (
                        <div className="flex h-full flex-col items-center justify-center text-center">
                            <div className="mb-4 grid size-16 place-items-center rounded-full bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-800 dark:to-zinc-700">
                                <UserIcon
                                    size={24}
                                    className="text-zinc-500 dark:text-zinc-400"
                                />
                            </div>
                            <p className="mb-2 text-lg font-medium text-zinc-900 dark:text-zinc-100">
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
                                >
                                    <div
                                        className={`flex max-w-[85%] min-w-0 gap-3 md:max-w-[70%] ${isOwn ? 'flex-row-reverse' : ''}`}
                                    >
                                        <div className="flex-shrink-0">
                                            <UserAvatar
                                                avatarUrl={
                                                    message.user.avatar_url
                                                }
                                                userName={message.user.name}
                                                className="size-8"
                                                fallbackClassName="text-xs"
                                            />
                                        </div>
                                        <div
                                            className={`flex flex-col ${isOwn ? 'items-end' : 'items-start'}`}
                                        >
                                            <div
                                                className={`rounded-2xl px-4 py-3 text-sm leading-relaxed ${
                                                    isOwn
                                                        ? 'bg-gradient-to-r from-purple-500 to-purple-700 text-white'
                                                        : 'border border-zinc-200 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100'
                                                }`}
                                                style={{
                                                    wordBreak: 'break-word',
                                                    overflowWrap: 'anywhere',
                                                }}
                                            >
                                                {message.content}
                                            </div>
                                            <div
                                                className={`mt-1 text-xs text-zinc-500 dark:text-zinc-400 ${isOwn ? 'mr-1' : 'ml-1'}`}
                                            >
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
                <div className="border-t border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            handleSendMessage(e);
                        }}
                        className="flex items-end gap-3"
                    >
                        <textarea
                            ref={textareaRef}
                            value={newMessage}
                            onChange={(e) => setNewMessage(e.target.value)}
                            onKeyDown={(e) => {
                                if (e.key === 'Enter' && !e.shiftKey) {
                                    e.preventDefault();
                                    const form = e.currentTarget.form;
                                    if (form) {
                                        form.requestSubmit();
                                    }
                                }
                            }}
                            placeholder="Type a message..."
                            className="flex-1 resize-none rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder-zinc-500 transition-all focus:border-purple-500 focus:ring-2 focus:ring-purple-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100 dark:placeholder-zinc-400 dark:focus:border-zinc-400 dark:focus:ring-zinc-400"
                            disabled={sending}
                            autoComplete="off"
                            inputMode="text"
                            enterKeyHint="send"
                            rows={1}
                            style={{
                                minHeight: '44px',
                                maxHeight: '120px',
                                height: 'auto',
                            }}
                            onInput={(e) => {
                                const target = e.target as HTMLTextAreaElement;
                                target.style.height = 'auto';
                                target.style.height =
                                    Math.min(target.scrollHeight, 120) + 'px';
                            }}
                        />
                        <button
                            type="submit"
                            disabled={!newMessage.trim() || sending}
                            className="hidden rounded-2xl bg-gradient-to-r from-purple-500 to-purple-700 px-4 py-3 text-white shadow-lg transition-all duration-200 hover:from-purple-600 hover:to-purple-800 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50 md:block"
                            style={{ touchAction: 'manipulation' }}
                        >
                            <Send size={18} />
                        </button>
                    </form>
                </div>
            </div>
        </ChatLayout>
    );
}
