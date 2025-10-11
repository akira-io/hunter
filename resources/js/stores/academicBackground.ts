import { ProfileStoreTypes } from '@/types';
import { create, Mutate, StoreApi, UseBoundStore } from 'zustand';

export const useAcademicBackground: UseBoundStore<
    Mutate<StoreApi<ProfileStoreTypes>, []>
> = create((set) => ({
    isOpen: false,
    open: () => set({ isOpen: true }),
    close: () => set({ isOpen: false }),
    set: (isOpen: boolean) => set(() => ({ isOpen: isOpen })),
}));
