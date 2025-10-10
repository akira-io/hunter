import { create, Mutate, StoreApi, UseBoundStore } from 'zustand';

interface TweetStore {
    isFloatCreateTweetOpen: boolean;
    setIsFloatCreateTweetOpen: (isOpen: boolean) => void;
}

export const useTweetStore: UseBoundStore<Mutate<StoreApi<TweetStore>, []>> =
    create((set) => ({
        isFloatCreateTweetOpen: false,
        setIsFloatCreateTweetOpen: (condition: boolean) =>
            set({ isFloatCreateTweetOpen: condition }),
    }));
