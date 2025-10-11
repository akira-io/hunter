import { create, Mutate, StoreApi, UseBoundStore } from 'zustand';

interface HuntStore {
    isFloatCreateHuntOpen: boolean;
    setIsFloatCreateHuntOpen: (isOpen: boolean) => void;
}

export const useHuntStore: UseBoundStore<Mutate<StoreApi<HuntStore>, []>> =
    create((set) => ({
        isFloatCreateHuntOpen: false,
        setIsFloatCreateHuntOpen: (condition: boolean) =>
            set({ isFloatCreateHuntOpen: condition }),
    }));
