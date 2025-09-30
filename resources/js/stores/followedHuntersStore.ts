import { create } from 'zustand';
import { createJSONStorage, persist } from 'zustand/middleware';

interface Hunter {
    id: number;
    name: string;
    avatar_url?: string;
    username?: string;
    level?: number;
    is_online?: boolean;
}

interface FollowedHuntersState {
    hunters: Hunter[];
    loading: boolean;
    lastUpdated: number;

    // Actions
    setHunters: (hunters: Hunter[]) => void;
    updateHunterOnlineStatus: (hunterId: number, isOnline: boolean) => void;
    addHunter: (hunter: Hunter) => void;
    removeHunter: (hunterId: number) => void;
    setLoading: (loading: boolean) => void;
    clearHunters: () => void;
    refreshHunters: () => Promise<void>;
}

export const useFollowedHuntersStore = create<FollowedHuntersState>()(
    persist(
        (set, get) => ({
            hunters: [],
            loading: false,
            lastUpdated: 0,

            setHunters: (hunters: Hunter[]) => {
                set({
                    hunters,
                    lastUpdated: Date.now(),
                    loading: false,
                });
            },

            updateHunterOnlineStatus: (hunterId: number, isOnline: boolean) => {
                const { hunters } = get();
                const updatedHunters = hunters.map((hunter) => (hunter.id === hunterId ? { ...hunter, is_online: isOnline } : hunter));
                set({ hunters: updatedHunters });
            },

            addHunter: (hunter: Hunter) => {
                const { hunters } = get();
                const exists = hunters.some((h) => h.id === hunter.id);
                if (!exists) {
                    set({
                        hunters: [...hunters, hunter],
                        lastUpdated: Date.now(),
                    });
                }
            },

            removeHunter: (hunterId: number) => {
                const { hunters } = get();
                const hunter = hunters.find((h) => h.id === hunterId);
                if (hunter) {
                    set({
                        hunters: hunters.filter((h) => h.id !== hunterId),
                        lastUpdated: Date.now(),
                    });
                }
            },

            setLoading: (loading: boolean) => {
                set({ loading });
            },

            clearHunters: () => {
                set({
                    hunters: [],
                    loading: false,
                    lastUpdated: 0,
                });
            },

            refreshHunters: async () => {
                const { setLoading, setHunters } = get();
                setLoading(true);

                try {
                    const response = await fetch('/followed-hunters', {
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });

                    if (response.ok) {
                        const data = await response.json();
                        const hunters = Array.isArray(data) ? data : (data?.data ?? []);
                        setHunters(hunters);
                    } else {
                        setLoading(false);
                    }
                } catch {
                    setLoading(false);
                }
            },
        }),
        {
            name: 'devhunter-followed-hunters',
            storage: createJSONStorage(() => localStorage),

            // Only persist hunters and lastUpdated
            partialize: (state) => ({
                hunters: state.hunters,
                lastUpdated: state.lastUpdated,
            }),

            // Check if cached data is still valid (5 minutes)
            onRehydrateStorage: () => (state) => {
                if (state) {
                    const now = Date.now();
                    const maxAge = 5 * 60 * 1000; // 5 minutes

                    if (now - state.lastUpdated > maxAge) {
                        state.clearHunters();
                    } else {
                        // Reset loading on rehydration
                        state.loading = false;
                    }
                }
            },
        },
    ),
);

// Selector helpers for better performance
export const useFollowedHunters = () => useFollowedHuntersStore((state) => state.hunters);
export const useFollowedHuntersLoading = () => useFollowedHuntersStore((state) => state.loading);

// Individual action selectors
export const useSetFollowedHunters = () => useFollowedHuntersStore((state) => state.setHunters);
export const useUpdateHunterOnlineStatus = () => useFollowedHuntersStore((state) => state.updateHunterOnlineStatus);
export const useAddFollowedHunter = () => useFollowedHuntersStore((state) => state.addHunter);
export const useRemoveFollowedHunter = () => useFollowedHuntersStore((state) => state.removeHunter);
export const useRefreshFollowedHunters = () => useFollowedHuntersStore((state) => state.refreshHunters);
export const useClearFollowedHunters = () => useFollowedHuntersStore((state) => state.clearHunters);
