import { Toaster } from '@/components/ui/toaster';
import { ChatProvider, useChatContext } from '@/contexts/ChatContext';
import { useNotificationManager } from '@/hooks/useNotificationManager';
import { usePresenceManager } from '@/hooks/usePresenceManager';
import { useFollowedHunters } from '@/stores/followedHuntersStore';
import { useOnlineUsers } from '@/stores/onlineUsersStore';
import chat from '@/routes/chat';
import hunts from '@/routes/hunts';
import { type BreadcrumbItem, type Notification } from '@/types';
import { type Conversation, type User } from '@/types/chat';
import { Head, router, usePage } from '@inertiajs/react';
import { ArrowLeft, MessageCircle, User as UserIcon } from 'lucide-react';
import { type ReactNode, useMemo } from 'react';

interface ChatLayoutProps {
    children: ReactNode;
    title?: string;
    showSidebar?: boolean;
    conversationId?: number | null;
    breadcrumbs?: BreadcrumbItem[];
}

function ChatLayoutContent({ children, title = 'Chat', showSidebar = true, conversationId }: ChatLayoutProps) {
    const { conversations, loading } = useChatContext();
    const page = usePage<{ auth: { user?: User } }>();
    const currentUserId = page.props.auth.user?.id;

    const onlineUsers = useOnlineUsers();
    const followedHunters = useFollowedHunters();

    // On mobile, hide sidebar when a conversation is selected
    const showSidebarOnMobile = !conversationId;

    const handleConversationClick = (conversationId: number) => {
        router.visit(chat.show.url(conversationId));
    };

    const formatTime = (dateString?: string) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        const now = new Date();
        const diffTime = Math.abs(now.getTime() - date.getTime());
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays === 0) {
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
        if (diffDays === 1) {
            return 'Yesterday';
        }
        if (diffDays < 7) {
            return date.toLocaleDateString([], { weekday: 'short' });
        }
        return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
    };

    const getConversationTitle = (conversation: Conversation) => {
        if (conversation.title) {
            return conversation.title;
        }
        return conversation.other_participant?.name || 'Unknown User';
    };

    const isUserOnline = (userId: number) => {
        return onlineUsers.some((user) => user.id === userId) || followedHunters.some((hunter) => hunter.id === userId && hunter.is_online);
    };

    // Sort conversations: online users first, then by last message time
    const sortedConversations = useMemo(() => {
        return [...conversations].sort((a, b) => {
            const aParticipant = a.other_participant;
            const bParticipant = b.other_participant;

            const aIsOnline = aParticipant ? isUserOnline(aParticipant.id) : false;
            const bIsOnline = bParticipant ? isUserOnline(bParticipant.id) : false;

            // Online users first
            if (aIsOnline && !bIsOnline) return -1;
            if (!aIsOnline && bIsOnline) return 1;

            // If both online or both offline, sort by last message time
            const aTime = a.last_message_at ? new Date(a.last_message_at).getTime() : 0;
            const bTime = b.last_message_at ? new Date(b.last_message_at).getTime() : 0;
            return bTime - aTime;
        });
    }, [conversations, onlineUsers, followedHunters]); // eslint-disable-line react-hooks/exhaustive-deps

    return (
        <div className="flex h-screen flex-col bg-zinc-50 dark:bg-zinc-900">
            <Head title={title} />

            {/* Desktop and Mobile Responsive Layout */}
            <div className="flex h-full">
                {/* Sidebar - Conversations List (Hidden on mobile when conversation is selected) */}
                {showSidebar && (
                    <div
                        className={`flex w-full flex-col border-r border-zinc-200 bg-white md:w-80 dark:border-zinc-700 dark:bg-zinc-800 ${
                            showSidebarOnMobile ? '' : 'hidden md:flex'
                        }`}
                    >
                        {/* Sidebar Header */}
                        <div className="flex items-center justify-between border-b border-zinc-200 p-4 dark:border-zinc-700">
                            <h1 className="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Mensagens</h1>
                            <button
                                onClick={() => router.visit(hunts.index.url())}
                                className="rounded-lg p-2 text-zinc-600 transition-colors hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700"
                                aria-label="Back to home"
                            >
                                <ArrowLeft size={20} />
                            </button>
                        </div>

                        {/* Conversations List */}
                        <div className="flex-1 overflow-y-auto">
                            {loading ? (
                                <div className="space-y-2 p-4">
                                    {[...Array(5)].map((_, i) => (
                                        <div key={i} className="flex animate-pulse items-center gap-3 p-3">
                                            <div className="size-12 rounded-full bg-zinc-200 dark:bg-zinc-700"></div>
                                            <div className="flex-1">
                                                <div className="mb-2 h-4 w-3/4 rounded bg-zinc-200 dark:bg-zinc-700"></div>
                                                <div className="h-3 w-1/2 rounded bg-zinc-200 dark:bg-zinc-700"></div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : conversations.length === 0 ? (
                                <div className="flex flex-col items-center justify-center p-8 text-center">
                                    <div className="mb-4 grid size-16 place-items-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                        <MessageCircle size={32} className="text-zinc-400" />
                                    </div>
                                    <p className="text-lg font-medium text-zinc-900 dark:text-zinc-100">No conversations yet</p>
                                    <p className="text-sm text-zinc-500 dark:text-zinc-400">Start chatting with someone!</p>
                                </div>
                            ) : (
                                <div className="divide-y divide-zinc-100 dark:divide-zinc-700">
                                    {sortedConversations.map((conversation) => {
                                        const otherParticipant = conversation.other_participant;
                                        const isOnline = otherParticipant ? isUserOnline(otherParticipant.id) : false;

                                        return (
                                            <button
                                                key={conversation.id}
                                                onClick={() => handleConversationClick(conversation.id)}
                                                className={`flex w-full items-center gap-3 p-3 text-left transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-700/50 ${
                                                    conversationId === conversation.id ? 'bg-zinc-100 dark:bg-zinc-700' : ''
                                                }`}
                                            >
                                                <div className="relative flex-shrink-0">
                                                    {otherParticipant?.avatar_url ? (
                                                        <img
                                                            src={otherParticipant.avatar_url}
                                                            alt={otherParticipant.name}
                                                            className="size-12 rounded-full object-cover"
                                                        />
                                                    ) : (
                                                        <div className="flex size-12 items-center justify-center rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-600">
                                                            <UserIcon size={20} className="text-zinc-600 dark:text-zinc-300" />
                                                        </div>
                                                    )}
                                                    {/* Online Status Indicator */}
                                                    {conversation.type === 'direct' && (
                                                        <div
                                                            className={`absolute -right-0.5 -bottom-0.5 size-3 rounded-full border-2 border-white dark:border-zinc-800 ${
                                                                isOnline ? 'bg-emerald-400' : 'bg-zinc-400'
                                                            }`}
                                                        />
                                                    )}
                                                    {conversation.unread_count > 0 && (
                                                        <div className="absolute -top-1 -right-1 flex size-5 items-center justify-center rounded-full bg-purple-500 text-xs font-bold text-white">
                                                            {conversation.unread_count > 9 ? '9+' : conversation.unread_count}
                                                        </div>
                                                    )}
                                                </div>
                                                <div className="min-w-0 flex-1">
                                                    <div className="flex items-center justify-between gap-2">
                                                        <h3
                                                            className={`truncate text-sm font-medium ${
                                                                conversation.unread_count > 0
                                                                    ? 'text-zinc-900 dark:text-zinc-100'
                                                                    : 'text-zinc-700 dark:text-zinc-300'
                                                            }`}
                                                        >
                                                            {getConversationTitle(conversation)}
                                                        </h3>
                                                        <span className="flex-shrink-0 text-xs text-zinc-500 dark:text-zinc-400">
                                                            {formatTime(conversation.last_message_at)}
                                                        </span>
                                                    </div>
                                                    {conversation.last_message && (
                                                        <p
                                                            className={`mt-1 truncate text-sm ${
                                                                conversation.unread_count > 0
                                                                    ? 'font-medium text-zinc-700 dark:text-zinc-300'
                                                                    : 'text-zinc-500 dark:text-zinc-400'
                                                            }`}
                                                        >
                                                            {conversation.last_message.user.id === currentUserId && 'You: '}
                                                            {conversation.last_message.content}
                                                        </p>
                                                    )}
                                                </div>
                                            </button>
                                        );
                                    })}
                                </div>
                            )}
                        </div>
                    </div>
                )}

                {/* Main Chat Area (Hidden on mobile when no conversation is selected) */}
                <div className={`flex flex-1 flex-col ${conversationId ? '' : 'hidden md:flex'}`}>{children}</div>
            </div>

            <Toaster />
        </div>
    );
}

export default function ChatLayout({ children, ...props }: ChatLayoutProps) {
    const page = usePage<{
        auth: { user?: User };
        notifications?: { data: Notification[] };
        unread_count?: number;
    }>();

    const { auth, notifications, unread_count } = page.props;
    const currentUserId = useMemo(() => auth.user?.id, [auth.user?.id]);

    usePresenceManager({ currentUserId });
    useNotificationManager({
        currentUserId,
        notifications: notifications?.data,
        unreadCount: unread_count,
    });

    return (
        <ChatProvider currentUserId={currentUserId}>
            <ChatLayoutContent {...props}>{children}</ChatLayoutContent>
        </ChatProvider>
    );
}
