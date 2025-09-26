import { create } from 'zustand'
import { persist, createJSONStorage } from 'zustand/middleware'

interface Hunter {
    id: number
    name: string
    avatar_url?: string
    username?: string
    level?: number
    is_online?: boolean
}

interface FollowedHuntersState {
    hunters: Hunter[]
    loading: boolean
    lastUpdated: number

    // Actions
    setHunters: (hunters: Hunter[]) => void
    updateHunterOnlineStatus: (hunterId: number, isOnline: boolean) => void
    addHunter: (hunter: Hunter) => void
    removeHunter: (hunterId: number) => void
    setLoading: (loading: boolean) => void
    clearHunters: () => void
    refreshHunters: () => Promise<void>
}

export const useFollowedHuntersStore = create<FollowedHuntersState>()(
    persist(
        (set, get) => ({
            hunters: [],
            loading: false,
            lastUpdated: 0,

            setHunters: (hunters: Hunter[]) => {
                console.log('🔍 FollowedHuntersStore: Definindo hunters:', hunters.length)
                set({
                    hunters,
                    lastUpdated: Date.now(),
                    loading: false
                })
            },

            updateHunterOnlineStatus: (hunterId: number, isOnline: boolean) => {
                const { hunters } = get()
                const updatedHunters = hunters.map(hunter =>
                    hunter.id === hunterId
                        ? { ...hunter, is_online: isOnline }
                        : hunter
                )
                set({ hunters: updatedHunters })
            },

            addHunter: (hunter: Hunter) => {
                const { hunters } = get()
                const exists = hunters.some(h => h.id === hunter.id)
                if (!exists) {
                    console.log('🔍 FollowedHuntersStore: Adicionando hunter:', hunter.name)
                    set({
                        hunters: [...hunters, hunter],
                        lastUpdated: Date.now()
                    })
                }
            },

            removeHunter: (hunterId: number) => {
                const { hunters } = get()
                const hunter = hunters.find(h => h.id === hunterId)
                if (hunter) {
                    console.log('🔍 FollowedHuntersStore: Removendo hunter:', hunter.name)
                    set({
                        hunters: hunters.filter(h => h.id !== hunterId),
                        lastUpdated: Date.now()
                    })
                }
            },

            setLoading: (loading: boolean) => {
                set({ loading })
            },

            clearHunters: () => {
                console.log('🔍 FollowedHuntersStore: Limpando todos os hunters')
                set({
                    hunters: [],
                    loading: false,
                    lastUpdated: 0
                })
            },

            refreshHunters: async () => {
                console.log('🔍 FollowedHuntersStore: Iniciando busca de hunters seguidos...')
                const { setLoading, setHunters } = get()
                setLoading(true)

                try {
                    console.log('🔍 FollowedHuntersStore: Fazendo requisição para /followed-hunters')
                    const response = await fetch('/followed-hunters', {
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    })

                    console.log('🔍 FollowedHuntersStore: Resposta recebida:', response.status, response.ok)

                    if (response.ok) {
                        const data = await response.json()
                        console.log('🔍 FollowedHuntersStore: Dados recebidos:', data)
                        const hunters = Array.isArray(data) ? data : (data?.data ?? [])
                        setHunters(hunters)
                    } else {
                        console.error('🔍 FollowedHuntersStore: Erro ao buscar hunters:', response.status)
                        setLoading(false)
                    }
                } catch (error) {
                    console.error('🔍 FollowedHuntersStore: Erro na requisição:', error)
                    setLoading(false)
                }
            }
        }),
        {
            name: 'devhunter-followed-hunters',
            storage: createJSONStorage(() => localStorage),

            // Only persist hunters and lastUpdated
            partialize: (state) => ({
                hunters: state.hunters,
                lastUpdated: state.lastUpdated
            }),

            // Check if cached data is still valid (5 minutes)
            onRehydrateStorage: () => (state) => {
                console.log('🔍 FollowedHuntersStore: Rehydrating store...')
                if (state) {
                    const now = Date.now()
                    const maxAge = 5 * 60 * 1000 // 5 minutes
                    console.log('🔍 FollowedHuntersStore: State found, lastUpdated:', state.lastUpdated, 'now:', now, 'diff:', now - state.lastUpdated)

                    if (now - state.lastUpdated > maxAge) {
                        console.log('🔍 FollowedHuntersStore: Cache expirado, limpando...')
                        state.clearHunters()
                    } else {
                        console.log('🔍 FollowedHuntersStore: Cache válido, carregando', state.hunters.length, 'hunters')
                        // Reset loading on rehydration
                        state.loading = false
                    }
                } else {
                    console.log('🔍 FollowedHuntersStore: No cached state found')
                }
            }
        }
    )
)

// Selector helpers for better performance
export const useFollowedHunters = () => useFollowedHuntersStore(state => state.hunters)
export const useFollowedHuntersLoading = () => useFollowedHuntersStore(state => state.loading)

// Individual action selectors
export const useSetFollowedHunters = () => useFollowedHuntersStore(state => state.setHunters)
export const useUpdateHunterOnlineStatus = () => useFollowedHuntersStore(state => state.updateHunterOnlineStatus)
export const useAddFollowedHunter = () => useFollowedHuntersStore(state => state.addHunter)
export const useRemoveFollowedHunter = () => useFollowedHuntersStore(state => state.removeHunter)
export const useRefreshFollowedHunters = () => useFollowedHuntersStore(state => state.refreshHunters)
export const useClearFollowedHunters = () => useFollowedHuntersStore(state => state.clearHunters)