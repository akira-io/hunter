import { type Conversation, type User } from '@/types/chat';
import { useMemo, useState } from 'react';

interface UseChatSearchOptions {
    conversations: Conversation[];
    onlineUsers?: User[];
    followedHunters?: Array<{ id: number; is_online?: boolean }>;
    currentUserId?: number;
}

interface UseChatSearchReturn {
    searchQuery: string;
    setSearchQuery: (query: string) => void;
    isSearchFocused: boolean;
    setIsSearchFocused: (focused: boolean) => void;
    filteredConversations: Conversation[];
    clearSearch: () => void;
    hasResults: boolean;
    isSearching: boolean;
}

export const useChatSearch = ({
    conversations,
    onlineUsers = [],
    followedHunters = [],
}: UseChatSearchOptions): UseChatSearchReturn => {
    const [searchQuery, setSearchQuery] = useState('');
    const [isSearchFocused, setIsSearchFocused] = useState(false);

    const isUserOnline = (userId: number): boolean => {
        return (
            onlineUsers.some((user) => user.id === userId) ||
            followedHunters.some(
                (hunter) => hunter.id === userId && hunter.is_online,
            )
        );
    };

    const getConversationTitle = (conversation: Conversation): string => {
        if (conversation.title) {
            return conversation.title;
        }
        return conversation.other_participant?.name || 'Unknown User';
    };

    const filteredConversations = useMemo(() => {
        let filtered = [...conversations];

        // Apply search filter
        if (searchQuery.trim()) {
            const query = searchQuery.toLowerCase();
            filtered = filtered.filter((conversation) => {
                const title = getConversationTitle(conversation).toLowerCase();
                const lastMessage =
                    conversation.last_message?.content.toLowerCase() || '';
                return title.includes(query) || lastMessage.includes(query);
            });
        }

        // Sort filtered results: online users first, then by last message time
        return filtered.sort((a, b) => {
            const aParticipant = a.other_participant;
            const bParticipant = b.other_participant;

            const aIsOnline = aParticipant
                ? isUserOnline(aParticipant.id)
                : false;
            const bIsOnline = bParticipant
                ? isUserOnline(bParticipant.id)
                : false;

            // Online users first
            if (aIsOnline && !bIsOnline) return -1;
            if (!aIsOnline && bIsOnline) return 1;

            // If both online or both offline, sort by last message time
            const aTime = a.last_message_at
                ? new Date(a.last_message_at).getTime()
                : 0;
            const bTime = b.last_message_at
                ? new Date(b.last_message_at).getTime()
                : 0;
            return bTime - aTime;
        });
    }, [conversations, onlineUsers, followedHunters, searchQuery]); // eslint-disable-line react-hooks/exhaustive-deps

    const clearSearch = () => {
        setSearchQuery('');
    };

    const hasResults = filteredConversations.length > 0;
    const isSearching = searchQuery.trim().length > 0;

    return {
        searchQuery,
        setSearchQuery,
        isSearchFocused,
        setIsSearchFocused,
        filteredConversations,
        clearSearch,
        hasResults,
        isSearching,
    };
};
