import { useChatContext } from '@/contexts/ChatContext';
import { shouldUseMobileChat } from '@/hooks/useDeviceDetection';
import { useFollowedHunters, useFollowedHuntersLoading } from '@/stores/followedHuntersStore';
import { useIsConnected, useOnlineUsers } from '@/stores/onlineUsersStore';
import { router } from '@inertiajs/react';
import { Search, UserCheck, User as UserIcon, Users, Wifi, WifiOff, XIcon } from 'lucide-react';
import React, { useState } from 'react';

interface ChatUsersProps {
    currentUserId?: number;
}

export const OnlineUsers: React.FC<ChatUsersProps> = ({ currentUserId }) => {
    const { openChatWindow, createConversation, conversations } = useChatContext();
    const onlineUsers = useOnlineUsers();
    const isConnected = useIsConnected();
    const followedHunters = useFollowedHunters();
    const followedHuntersLoading = useFollowedHuntersLoading();
    const [isOpen, setIsOpen] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');

    // Calculate total unread messages
    const totalUnreadCount = conversations.reduce((total, conv) => {
        return total + (conv.unread_count || 0);
    }, 0);

    // Filter online users (excluding current user)
    const filteredOnlineUsers = onlineUsers
        .filter((user) => user.id !== currentUserId)
        .filter((user) => user.name.toLowerCase().includes(searchTerm.toLowerCase()));

    // Filter followed hunters that are offline (excluding current user and online users)
    const offlineFollowedHunters = followedHunters
        .filter((hunter) => hunter.id !== currentUserId)
        .filter((hunter) => !filteredOnlineUsers.some((user) => user.id === hunter.id)) // Exclude already online users
        .filter(
            (hunter) =>
                hunter.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                (hunter.username && hunter.username.toLowerCase().includes(searchTerm.toLowerCase())),
        );

    // Combined list: online users first, then offline followed hunters (remove duplicates)
    const allUsers = [
        ...filteredOnlineUsers,
        ...offlineFollowedHunters.filter((hunter) => !filteredOnlineUsers.some((onlineUser) => onlineUser.id === hunter.id)),
    ];

    // Helper function to get unread count for a user
    const getUnreadCountForUser = (userId: number) => {
        if (!currentUserId) return 0;

        // Find direct conversation with this user
        const conversation = conversations.find(
            (conv) =>
                conv.type === 'direct' && conv.participants.some((p) => p.id === userId) && conv.participants.some((p) => p.id === currentUserId),
        );

        return conversation?.unread_count || 0;
    };

    // Don't show anything if no current user
    if (!currentUserId) {
        return null;
    }
    const handleUserClick = async (userId: number) => {
        try {
            const conversation = await createConversation('direct', [userId]);

            if (!conversation?.id) return;
            // Check if should use mobile chat
            if (shouldUseMobileChat()) return router.visit(`/chat/mobile/${conversation.id}`);

            openChatWindow(conversation.id);
        } catch (error) {
            console.error(error);
        }
    };

    return (
        <div className="fixed right-6 bottom-6 z-50">
            {/* Avatar Button with Badge */}
            <div className="relative">
                <button
                    type="button"
                    onClick={() => setIsOpen(!isOpen)}
                    className="relative size-14 rounded-full bg-gradient-to-br from-purple-500 to-purple-700 shadow-lg transition-all duration-200 hover:scale-105 hover:shadow-xl active:scale-95"
                >
                    <div className="absolute inset-0 rounded-full bg-white/10 backdrop-blur-sm" />
                    <div className="relative flex h-full items-center justify-center">
                        <Users size={24} className="text-white" />
                    </div>

                    {/* Badge with unread messages count */}
                    {totalUnreadCount > 0 && (
                        <div
                            className="absolute -top-1 -right-1 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full border-2 border-white bg-red-500 px-1 text-xs font-bold text-white dark:border-zinc-900"
                            style={{ zIndex: 60 }}
                        >
                            {totalUnreadCount > 99 ? '99+' : totalUnreadCount}
                        </div>
                    )}

                    {/* Online indicator */}
                    {filteredOnlineUsers.length > 0 && (
                        <div className="absolute -bottom-0.5 -left-0.5 size-4 animate-pulse rounded-full border-2 border-white bg-emerald-400 dark:border-zinc-900" />
                    )}
                </button>

                {/* Users List - Animated Dropdown */}
                <div
                    className={`absolute right-0 bottom-16 w-80 origin-bottom-right transition-all duration-300 ease-out ${
                        isOpen ? 'translate-y-0 scale-100 opacity-100' : 'pointer-events-none translate-y-2 scale-95 opacity-0'
                    }`}
                >
                    <div className="overflow-hidden rounded-2xl border border-zinc-200 bg-white/95 shadow-2xl backdrop-blur-lg dark:border-zinc-700 dark:bg-zinc-900/95">
                        {/* Header */}
                        <div className="border-b border-zinc-200 bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 dark:border-zinc-700 dark:from-zinc-800 dark:to-zinc-800">
                            <div className="mb-3 flex items-center justify-between">
                                <div className="flex items-center gap-2">
                                    <Users size={16} className="text-zinc-600 dark:text-zinc-400" />
                                    <span className="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Chat & Hunters</span>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => setIsOpen(false)}
                                    className="rounded-full p-1 transition-colors hover:bg-zinc-200 dark:hover:bg-zinc-700"
                                >
                                    <XIcon size={16} className="text-zinc-600 dark:text-zinc-400" />
                                </button>
                            </div>

                            {/* Connection Status */}
                            <div className="mb-3 flex items-center gap-2">
                                <div
                                    className={`size-2 rounded-full ${isConnected ? 'animate-pulse bg-emerald-400' : 'animate-bounce bg-red-400'}`}
                                />
                                <span className="text-xs text-zinc-600 dark:text-zinc-400">
                                    {isConnected ? 'Conectado ao chat' : 'Conectando...'}
                                </span>
                                <span className="ml-auto text-xs text-zinc-500 dark:text-zinc-400">
                                    {filteredOnlineUsers.length} online, {offlineFollowedHunters.length} offline
                                </span>
                            </div>

                            {/* Search Input */}
                            <div className="relative">
                                <Search size={16} className="absolute top-1/2 left-3 -translate-y-1/2 transform text-zinc-400 dark:text-zinc-500" />
                                <input
                                    type="text"
                                    placeholder="Pesquisar pessoas..."
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                    className="w-full rounded-lg border border-zinc-200 bg-white py-2 pr-4 pl-10 text-sm text-zinc-900 placeholder-zinc-500 focus:border-transparent focus:ring-2 focus:ring-purple-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100 dark:placeholder-zinc-400 dark:focus:ring-purple-400"
                                />
                            </div>
                        </div>

                        {/* Users List */}
                        {allUsers.length > 0 ? (
                            <div className="scrollbar-thin scrollbar-thumb-zinc-300 dark:scrollbar-thumb-zinc-600 scrollbar-track-transparent h-60 overflow-y-auto md:h-100">
                                {filteredOnlineUsers.length > 0 && (
                                    <div className="border-b border-emerald-100 bg-emerald-50 px-4 py-2 dark:border-emerald-800 dark:bg-emerald-900/20">
                                        <div className="flex items-center gap-2">
                                            <Wifi size={12} className="text-emerald-600 dark:text-emerald-400" />
                                            <span className="text-xs font-medium text-emerald-700 dark:text-emerald-300">
                                                Online ({filteredOnlineUsers.length})
                                            </span>
                                        </div>
                                    </div>
                                )}

                                {allUsers.map((item, index) => {
                                    const isOnline = filteredOnlineUsers.some(user => user.id === item.id);
                                    const displayName = item.name;
                                    const showOfflineHeader = !isOnline && index === filteredOnlineUsers.length && offlineFollowedHunters.length > 0;
                                    const unreadCount = getUnreadCountForUser(item.id);
                                    const avatarUrl = item.avatar_url;

                                    return (
                                        <React.Fragment key={item.id}>
                                            {showOfflineHeader && (
                                                <div className="border-b border-zinc-100 bg-zinc-50 px-4 py-2 dark:border-zinc-700 dark:bg-zinc-800/40">
                                                    <div className="flex items-center gap-2">
                                                        <UserCheck size={12} className="text-zinc-500 dark:text-zinc-400" />
                                                        <span className="text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                                            Seguindo ({offlineFollowedHunters.length})
                                                        </span>
                                                    </div>
                                                </div>
                                            )}
                                            <button
                                                type="button"
                                                className={`flex w-full cursor-pointer items-center gap-3 border-b border-zinc-100 p-4 text-left transition-colors duration-150 last:border-b-0 hover:bg-zinc-50 focus:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800/60 dark:focus:bg-zinc-800/60 ${
                                                    unreadCount > 0 ? 'border-l-2 border-l-red-400 bg-red-50/50 dark:bg-red-900/10' : ''
                                                }`}
                                                onClick={async () => {
                                                    await handleUserClick(item.id);
                                                    setIsOpen(false);
                                                }}
                                            >
                                                <div className="relative shrink-0">
                                                    {avatarUrl ? (
                                                        <>
                                                            <img
                                                                src={avatarUrl}
                                                                alt={displayName}
                                                                className="size-10 rounded-full object-cover ring-2 ring-white dark:ring-zinc-800"
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
                                                        <div className="grid size-10 place-items-center rounded-full bg-gradient-to-br from-zinc-200 to-zinc-300 ring-2 ring-white dark:from-zinc-700 dark:to-zinc-600 dark:ring-zinc-800">
                                                            <UserIcon size={20} className="text-zinc-600 dark:text-zinc-300" />
                                                        </div>
                                                    )}

                                                    {/* Status indicator */}
                                                    {isOnline ? (
                                                        <span className="absolute -right-0.5 -bottom-0.5 size-3.5 rounded-full bg-emerald-400 ring-2 ring-white dark:ring-zinc-900" />
                                                    ) : (
                                                        <span className="absolute -right-0.5 -bottom-0.5 size-3.5 rounded-full bg-zinc-400 ring-2 ring-white dark:ring-zinc-900" />
                                                    )}
                                                </div>

                                                <div className="min-w-0 flex-1">
                                                    <div className="flex items-center gap-2">
                                                        <div className="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                            {displayName}
                                                        </div>
                                                        {unreadCount > 0 && (
                                                            <div className="flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-bold text-white">
                                                                {unreadCount > 99 ? '99+' : unreadCount}
                                                            </div>
                                                        )}
                                                    </div>
                                                    <div
                                                        className={`flex items-center gap-1 text-xs font-medium ${
                                                            isOnline ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-500 dark:text-zinc-400'
                                                        }`}
                                                    >
                                                        {isOnline ? (
                                                            <>
                                                                <Wifi size={10} />
                                                                Online
                                                            </>
                                                        ) : (
                                                            <>
                                                                <WifiOff size={10} />
                                                                Offline
                                                            </>
                                                        )}
                                                        {!isOnline && 'level' in item && item.level && (
                                                            <span className="ml-1 text-purple-600 dark:text-purple-400">• Nível {item.level}</span>
                                                        )}
                                                    </div>
                                                </div>

                                                <div className={`size-2 rounded-full ${isOnline ? 'animate-pulse bg-emerald-400' : 'bg-zinc-400'}`} />
                                            </button>
                                        </React.Fragment>
                                    );
                                })}
                            </div>
                        ) : (
                            <div className="h-60 p-6 text-center md:h-100">
                                <Users size={32} className="mx-auto mb-2 text-zinc-400 dark:text-zinc-600" />
                                <p className="text-sm text-zinc-500 dark:text-zinc-400">
                                    {searchTerm
                                        ? `Nenhuma pessoa encontrada para "${searchTerm}"`
                                        : followedHuntersLoading
                                          ? 'Carregando...'
                                          : 'Nenhum usuário disponível'}
                                </p>
                                {!searchTerm && !followedHuntersLoading && (
                                    <p className="mt-2 text-xs text-zinc-400 dark:text-zinc-500">
                                        Usuários online e hunters seguidos aparecerão aqui
                                    </p>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};
