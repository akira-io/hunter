import { create } from 'zustand';
import { createJSONStorage, persist } from 'zustand/middleware';

export interface RecentSearch {
    query: string;
    timestamp: number;
}

interface RecentSearchesState {
    searches: RecentSearch[];
    maxSearches: number;

    addSearch: (query: string) => void;
    removeSearch: (query: string) => void;
    clearAll: () => void;
}

export const useRecentSearchesStore = create<RecentSearchesState>()(
    persist(
        (set, get) => ({
            searches: [],
            maxSearches: 10,

            addSearch: (query: string) => {
                const trimmedQuery = query.trim();

                if (!trimmedQuery || trimmedQuery.length < 2) {
                    return;
                }

                const { searches, maxSearches } = get();

                const filteredSearches = searches.filter((s) => s.query !== trimmedQuery);

                const newSearches = [{ query: trimmedQuery, timestamp: Date.now() }, ...filteredSearches].slice(0, maxSearches); // Keep only the most recent maxSearches

                set({ searches: newSearches });
            },

            removeSearch: (query: string) => {
                const { searches } = get();
                set({
                    searches: searches.filter((s) => s.query !== query),
                });
            },

            clearAll: () => {
                set({ searches: [] });
            },
        }),
        {
            name: 'devhunter-recent-searches',
            storage: createJSONStorage(() => localStorage),

            partialize: (state) => ({
                searches: state.searches,
            }),
        },
    ),
);

// Selector helpers for better performance
export const useRecentSearches = () => useRecentSearchesStore((state) => state.searches);
export const useAddSearch = () => useRecentSearchesStore((state) => state.addSearch);
export const useRemoveSearch = () => useRecentSearchesStore((state) => state.removeSearch);
export const useClearAllSearches = () => useRecentSearchesStore((state) => state.clearAll);
