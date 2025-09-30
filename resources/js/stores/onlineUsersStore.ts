import { create } from 'zustand';
import { createJSONStorage, persist } from 'zustand/middleware';

interface OnlineUser {
    id: number;
    name: string;
    avatar_url?: string;
    status: 'online' | 'offline';
}

interface OnlineUsersState {
    users: OnlineUser[];
    isConnected: boolean;
    lastUpdated: number;

    // Actions
    setUsers: (users: OnlineUser[]) => void;
    addUser: (user: OnlineUser) => void;
    removeUser: (userId: number) => void;
    setConnected: (connected: boolean) => void;
    clearUsers: () => void;
}

export const useOnlineUsersStore = create<OnlineUsersState>()(
    persist(
        (set, get) => ({
            users: [],
            isConnected: false,
            lastUpdated: 0,

            setUsers: (users: OnlineUser[]) => {
                set({
                    users: users.map((u) => ({ ...u, status: 'online' as const })),
                    lastUpdated: Date.now(),
                });
            },

            addUser: (user: OnlineUser) => {
                const { users } = get();
                const exists = users.some((u) => u.id === user.id);
                if (!exists) {
                    set({
                        users: [...users, { ...user, status: 'online' as const }],
                        lastUpdated: Date.now(),
                    });
                }
            },

            removeUser: (userId: number) => {
                const { users } = get();
                const user = users.find((u) => u.id === userId);
                if (user) {
                    set({
                        users: users.filter((u) => u.id !== userId),
                        lastUpdated: Date.now(),
                    });
                }
            },

            setConnected: (connected: boolean) => {
                set({ isConnected: connected });
            },

            clearUsers: () => {
                set({
                    users: [],
                    isConnected: false,
                    lastUpdated: 0,
                });
            },
        }),
        {
            name: 'devhunter-online-users',
            storage: createJSONStorage(() => localStorage),

            // Only persist users and lastUpdated, not connection status
            partialize: (state) => ({
                users: state.users,
                lastUpdated: state.lastUpdated,
            }),

            // Check if cached data is still valid (30 seconds)
            onRehydrateStorage: () => (state) => {
                if (state) {
                    const now = Date.now();
                    const maxAge = 30 * 1000; // 30 seconds

                    if (now - state.lastUpdated > maxAge) {
                        state.clearUsers();
                    } else {
                        // Reset connection status on rehydration
                        state.isConnected = false;
                    }
                }
            },
        },
    ),
);

// Selector helpers for better performance
export const useOnlineUsers = () => useOnlineUsersStore((state) => state.users);
export const useIsConnected = () => useOnlineUsersStore((state) => state.isConnected);

// Individual action selectors to avoid recreating objects
export const useSetUsers = () => useOnlineUsersStore((state) => state.setUsers);
export const useAddUser = () => useOnlineUsersStore((state) => state.addUser);
export const useRemoveUser = () => useOnlineUsersStore((state) => state.removeUser);
export const useSetConnected = () => useOnlineUsersStore((state) => state.setConnected);
export const useClearUsers = () => useOnlineUsersStore((state) => state.clearUsers);
