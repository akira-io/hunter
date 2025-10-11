import { Toaster } from '@/components/ui/toaster';
import { ChatProvider, useChatContext } from '@/contexts/ChatContext';
import { useChatSearch } from '@/hooks/use-chat-search';
import { useNotificationManager } from '@/hooks/use-notification-manager';
import { usePresenceManager } from '@/hooks/use-presence-manager';
import { useTimeFormatting } from '@/hooks/use-time-formatting';
import chat from '@/routes/chat';
import finder from '@/routes/finder';
import hunts from '@/routes/hunts';
import { useFollowedHunters } from '@/stores/followedHuntersStore';
import { useOnlineUsers } from '@/stores/onlineUsersStore';
import { type BreadcrumbItem, type Notification } from '@/types';
import { type Conversation, type User } from '@/types/chat';
import { Head, router, usePage } from '@inertiajs/react';
import {
    ArrowLeft,
    MessageCircle,
    MessageSquarePlus,
    Search,
    User as UserIcon,
    X,
} from 'lucide-react';
import { type ReactNode, useMemo } from 'react';

interface ChatLayoutProps {
    children: ReactNode;
    title?: string;
    showSidebar?: boolean;
    conversationId?: number | null;
    breadcrumbs?: BreadcrumbItem[];
}

function ChatLayoutContent({
    children,
    title = 'Chat',
    showSidebar = true,
    conversationId,
}: ChatLayoutProps) {
    const { conversations, loading } = useChatContext();
    const page = usePage<{ auth: { user?: User } }>();
    const currentUserId = page.props.auth.user?.id;

    const onlineUsers = useOnlineUsers();
    const followedHunters = useFollowedHunters();

    const {
        searchQuery,
        setSearchQuery,
        isSearchFocused,
        setIsSearchFocused,
        filteredConversations,
        clearSearch,
        hasResults,
        isSearching,
    } = useChatSearch({
        conversations,
        onlineUsers,
        followedHunters,
        currentUserId,
    });

    const { formatRelativeTime } = useTimeFormatting();

    // On mobile, hide sidebar when a conversation is selected
    const showSidebarOnMobile = !conversationId;

    const handleConversationClick = (conversationId: number) => {
        router.visit(chat.show.url(conversationId));
    };

    const getConversationTitle = (conversation: Conversation) => {
        if (conversation.title) {
            return conversation.title;
        }
        return conversation.other_participant?.name || 'Unknown User';
    };

    const isUserOnline = (userId: number) => {
        return (
            onlineUsers.some((user) => user.id === userId) ||
            followedHunters.some(
                (hunter) => hunter.id === userId && hunter.is_online,
            )
        );
    };

    const handleNewConversation = () => {
        router.visit(finder.index.url());
    };

    return (
        <div className="flex h-screen max-h-screen flex-col overflow-hidden bg-zinc-50 dark:bg-zinc-900">
            <Head title={title} />

            {/* Desktop and Mobile Responsive Layout */}
            <div className="flex h-full min-h-0">
                {/* Sidebar - Conversations List (Hidden on mobile when conversation is selected) */}
                {showSidebar && (
                    <div
                        className={`flex w-full flex-col border-r border-zinc-200 bg-white md:w-80 dark:border-zinc-700 dark:bg-zinc-800 ${
                            showSidebarOnMobile ? '' : 'hidden md:flex'
                        }`}
                    >
                        {/* Sidebar Header */}
                        <div
                            className="border-b border-zinc-200 dark:border-zinc-700"
                            style={{
                                paddingTop: 'env(safe-area-inset-top, 0px)',
                            }}
                        >
                            <div className="flex items-center justify-between p-4">
                                <button
                                    onClick={() =>
                                        router.visit(hunts.index.url())
                                    }
                                    className="rounded-lg p-2 text-zinc-600 transition-colors hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700"
                                    aria-label="Voltar para home"
                                    title="Voltar para home"
                                >
                                    <ArrowLeft size={20} />
                                </button>
                                <h1 className="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                                    Mensagens
                                </h1>
                                <div className="flex items-center gap-1">
                                    <button
                                        onClick={handleNewConversation}
                                        className="rounded-lg p-2 text-zinc-600 transition-colors hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700"
                                        aria-label="Nova conversa"
                                        title="Nova conversa"
                                    >
                                        <MessageSquarePlus size={20} />
                                    </button>
                                </div>
                            </div>

                            {/* Search Bar */}
                            <div className="px-4 pb-4">
                                <div
                                    className={`flex items-center gap-2 rounded-lg border bg-zinc-50 px-3 py-2 transition-all dark:bg-zinc-900 ${
                                        isSearchFocused
                                            ? 'border-purple-500 ring-2 ring-purple-500/20 dark:border-purple-400'
                                            : 'border-zinc-200 dark:border-zinc-700'
                                    }`}
                                >
                                    <Search
                                        size={18}
                                        className="flex-shrink-0 text-zinc-400 dark:text-zinc-500"
                                    />
                                    <input
                                        type="text"
                                        placeholder="Procurar conversas..."
                                        value={searchQuery}
                                        onChange={(e) =>
                                            setSearchQuery(e.target.value)
                                        }
                                        onFocus={() => setIsSearchFocused(true)}
                                        onBlur={() => setIsSearchFocused(false)}
                                        className="flex-1 bg-transparent text-sm text-zinc-900 placeholder-zinc-500 outline-none dark:text-zinc-100 dark:placeholder-zinc-400"
                                    />
                                    {searchQuery && (
                                        <button
                                            onClick={clearSearch}
                                            className="flex-shrink-0 rounded-full p-1 transition-colors hover:bg-zinc-200 dark:hover:bg-zinc-700"
                                            aria-label="Limpar busca"
                                        >
                                            <X
                                                size={14}
                                                className="text-zinc-500 dark:text-zinc-400"
                                            />
                                        </button>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Conversations List */}
                        <div className="flex-1 overflow-y-auto">
                            {loading ? (
                                <div className="space-y-2 p-4">
                                    {[...Array(5)].map((_, i) => (
                                        <div
                                            key={i}
                                            className="flex animate-pulse items-center gap-3 p-3"
                                        >
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
                                        <MessageCircle
                                            size={32}
                                            className="text-zinc-400"
                                        />
                                    </div>
                                    <p className="text-lg font-medium text-zinc-900 dark:text-zinc-100">
                                        Nenhuma conversa ainda
                                    </p>
                                    <p className="mb-4 text-sm text-zinc-500 dark:text-zinc-400">
                                        Comece a conversar com alguém!
                                    </p>
                                    <button
                                        onClick={handleNewConversation}
                                        className="flex items-center gap-2 rounded-lg bg-purple-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-purple-600"
                                    >
                                        <MessageSquarePlus size={18} />
                                        Nova conversa
                                    </button>
                                </div>
                            ) : !hasResults && isSearching ? (
                                <div className="flex flex-col items-center justify-center p-8 text-center">
                                    <div className="mb-4 grid size-16 place-items-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                        <Search
                                            size={32}
                                            className="text-zinc-400"
                                        />
                                    </div>
                                    <p className="text-lg font-medium text-zinc-900 dark:text-zinc-100">
                                        Nenhum resultado
                                    </p>
                                    <p className="mb-4 text-sm text-zinc-500 dark:text-zinc-400">
                                        Não encontramos conversas para "
                                        {searchQuery}"
                                    </p>
                                    <button
                                        onClick={clearSearch}
                                        className="text-sm font-medium text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300"
                                    >
                                        Limpar busca
                                    </button>
                                </div>
                            ) : (
                                <div className="divide-y divide-zinc-100 dark:divide-zinc-700">
                                    {filteredConversations.map(
                                        (conversation) => {
                                            const otherParticipant =
                                                conversation.other_participant;
                                            const isOnline = otherParticipant
                                                ? isUserOnline(
                                                      otherParticipant.id,
                                                  )
                                                : false;

                                            return (
                                                <button
                                                    key={conversation.id}
                                                    onClick={() =>
                                                        handleConversationClick(
                                                            conversation.id,
                                                        )
                                                    }
                                                    onTouchEnd={(e) => {
                                                        e.preventDefault();
                                                        handleConversationClick(
                                                            conversation.id,
                                                        );
                                                    }}
                                                    className={`flex w-full items-center gap-3 p-3 text-left transition-colors hover:bg-zinc-50 active:bg-zinc-100 dark:hover:bg-zinc-700/50 dark:active:bg-zinc-700 ${
                                                        conversationId ===
                                                        conversation.id
                                                            ? 'bg-zinc-100 dark:bg-zinc-700'
                                                            : ''
                                                    }`}
                                                    style={{
                                                        touchAction:
                                                            'manipulation',
                                                        WebkitTapHighlightColor:
                                                            'transparent',
                                                    }}
                                                >
                                                    <div className="relative flex-shrink-0">
                                                        {otherParticipant?.avatar_url ? (
                                                            <img
                                                                src={
                                                                    otherParticipant.avatar_url
                                                                }
                                                                alt={
                                                                    otherParticipant.name
                                                                }
                                                                className="size-12 rounded-full object-cover"
                                                            />
                                                        ) : (
                                                            <div className="flex size-12 items-center justify-center rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-600">
                                                                <UserIcon
                                                                    size={20}
                                                                    className="text-zinc-600 dark:text-zinc-300"
                                                                />
                                                            </div>
                                                        )}
                                                        {/* Online Status Indicator */}
                                                        {conversation.type ===
                                                            'direct' && (
                                                            <div
                                                                className={`absolute -right-0.5 -bottom-0.5 size-3 rounded-full border-2 border-white dark:border-zinc-800 ${
                                                                    isOnline
                                                                        ? 'bg-emerald-400'
                                                                        : 'bg-zinc-400'
                                                                }`}
                                                            />
                                                        )}
                                                        {conversation.unread_count >
                                                            0 && (
                                                            <div className="absolute -top-1 -right-1 flex size-5 items-center justify-center rounded-full bg-purple-500 text-xs font-bold text-white">
                                                                {conversation.unread_count >
                                                                9
                                                                    ? '9+'
                                                                    : conversation.unread_count}
                                                            </div>
                                                        )}
                                                    </div>
                                                    <div className="min-w-0 flex-1">
                                                        <div className="flex items-center justify-between gap-2">
                                                            <h3
                                                                className={`truncate text-sm font-medium ${
                                                                    conversation.unread_count >
                                                                    0
                                                                        ? 'text-zinc-900 dark:text-zinc-100'
                                                                        : 'text-zinc-700 dark:text-zinc-300'
                                                                }`}
                                                            >
                                                                {getConversationTitle(
                                                                    conversation,
                                                                )}
                                                            </h3>
                                                            <span className="flex-shrink-0 text-xs text-zinc-500 dark:text-zinc-400">
                                                                {formatRelativeTime(
                                                                    conversation.last_message_at,
                                                                )}
                                                            </span>
                                                        </div>
                                                        {conversation.last_message && (
                                                            <p
                                                                className={`mt-1 truncate text-sm ${
                                                                    conversation.unread_count >
                                                                    0
                                                                        ? 'font-medium text-zinc-700 dark:text-zinc-300'
                                                                        : 'text-zinc-500 dark:text-zinc-400'
                                                                }`}
                                                            >
                                                                {conversation
                                                                    .last_message
                                                                    .user.id ===
                                                                    currentUserId &&
                                                                    'You: '}
                                                                {
                                                                    conversation
                                                                        .last_message
                                                                        .content
                                                                }
                                                            </p>
                                                        )}
                                                    </div>
                                                </button>
                                            );
                                        },
                                    )}
                                </div>
                            )}
                        </div>
                    </div>
                )}

                {/* Main Chat Area (Hidden on mobile when no conversation is selected) */}
                <div
                    className={`flex min-h-0 flex-1 flex-col overflow-hidden ${conversationId ? '' : 'hidden md:flex'}`}
                >
                    {children}
                </div>
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
