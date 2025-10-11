import { ProfileStoreTypes } from '@/types';
import { create, Mutate, StoreApi, UseBoundStore } from 'zustand';

export type LinkName =
    | 'GitHub'
    | 'Twitter'
    | 'YouTube'
    | 'LinkedIn'
    | 'Bluesky'
    | 'Website';

interface LinkData {
    github_url: string;
    twitter_url: string;
    youtube_url: string;
    linkedin_url: string;
    bluesky_url: string;
    website_url: string;
}

interface LinkStoreType extends ProfileStoreTypes {
    Link: Record<LinkName, keyof LinkData>;
}

export const useLinkStore: UseBoundStore<Mutate<StoreApi<LinkStoreType>, []>> =
    create((set) => ({
        isOpen: false,
        open: () => set({ isOpen: true }),
        close: () => set({ isOpen: false }),
        set: (isOpen: boolean) => set(() => ({ isOpen: isOpen })),
        Link: {
            GitHub: 'github_url',
            Twitter: 'twitter_url',
            YouTube: 'youtube_url',
            LinkedIn: 'linkedin_url',
            Bluesky: 'bluesky_url',
            Website: 'website_url',
        },
    }));
