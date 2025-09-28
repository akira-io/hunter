import React, { createContext, useContext, useState, useEffect } from 'react'
import { useChat } from '@/hooks/useChat'

interface User {
    id: number
    name: string
    avatar_url?: string
}

interface Message {
    id: number
    content: string
    type: 'text' | 'image' | 'file'
    metadata?: Record<string, unknown> | null
    created_at: string
    user: User
}

interface Conversation {
    id: number
    title: string
    type: 'direct' | 'group'
    participants: User[]
    last_message?: Message
    last_message_at?: string
    unread_count: number
    messages?: Message[]
}

interface ChatContextType {
    isChatOpen: boolean
    setChatOpen: (open: boolean) => void
    activeConversationId: number | null
    setActiveConversationId: (id: number | null) => void
    chatWindows: number[]
    backgroundWindows: number[]
    openChatWindow: (conversationId: number) => void
    closeChatWindow: (conversationId: number) => void
    closeBackgroundWindow: (conversationId: number) => void
    switchToWindow: (conversationId: number) => void
    minimizedWindows: Set<number>
    toggleMinimize: (conversationId: number) => void
    clearChatState: () => void
    currentUserId?: number
    // Chat data and functions
    conversations: Conversation[]
    activeConversation: Conversation | null
    loading: boolean
    sending: boolean
    loadConversations: () => Promise<void>
    loadConversation: (conversationId: number) => Promise<void>
    sendMessage: (conversationId: number, content: string, type?: 'text' | 'image' | 'file', metadata?: Record<string, unknown> | null) => Promise<void>
    createConversation: (type: 'direct' | 'group', participants: number[], title?: string) => Promise<any>
    markMessagesAsRead: (conversationId: number, messageIds?: number[]) => Promise<void>
    setActiveConversation: (conversation: Conversation | null) => void
}

const ChatContext = createContext<ChatContextType | undefined>(undefined)

export const useChatContext = () => {
    const context = useContext(ChatContext)
    if (!context) {
        throw new Error('useChatContext must be used within a ChatProvider')
    }
    return context
}

interface ChatProviderProps {
    children: React.ReactNode
    currentUserId?: number
}

const STORAGE_KEY = 'devhunter_chat_state'

interface ChatState {
    chatWindows: number[]
    backgroundWindows: number[]
    minimizedWindows: number[]
}

const loadChatState = (): ChatState => {
    try {
        const stored = localStorage.getItem(STORAGE_KEY)
        if (stored) {
            const parsed = JSON.parse(stored)
            return {
                chatWindows: parsed.chatWindows || [],
                backgroundWindows: parsed.backgroundWindows || [],
                minimizedWindows: parsed.minimizedWindows || []
            }
        }
    } catch (error) {
        console.error('Failed to load chat state from localStorage:', error)
    }
    return {
        chatWindows: [],
        backgroundWindows: [],
        minimizedWindows: []
    }
}

const saveChatState = (state: ChatState) => {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(state))
    } catch (error) {
        console.error('Failed to save chat state to localStorage:', error)
    }
}

export const ChatProvider: React.FC<ChatProviderProps> = ({ children, currentUserId }) => {
    const [isChatOpen, setChatOpen] = useState(false)
    const [activeConversationId, setActiveConversationId] = useState<number | null>(null)

    // Load initial state from localStorage
    const initialState = loadChatState()
    const [chatWindows, setChatWindows] = useState<number[]>(initialState.chatWindows)
    const [backgroundWindows, setBackgroundWindows] = useState<number[]>(initialState.backgroundWindows)
    const [minimizedWindows, setMinimizedWindows] = useState<Set<number>>(new Set(initialState.minimizedWindows))

    // Use the central chat hook
    const chatHook = useChat(currentUserId)

    // Save state to localStorage whenever it changes
    useEffect(() => {
        const state: ChatState = {
            chatWindows,
            backgroundWindows,
            minimizedWindows: Array.from(minimizedWindows)
        }
        saveChatState(state)
    }, [chatWindows, backgroundWindows, minimizedWindows])

    const openChatWindow = (conversationId: number) => {
        // Remove from background windows if exists
        setBackgroundWindows(prev => prev.filter(id => id !== conversationId))

        setChatWindows(prev => {
            if (prev.includes(conversationId)) {
                return prev // Already visible
            }

            if (prev.length < 1) {
                // Space available, just add
                return [...prev, conversationId]
            } else {
                // Move current to background, add new one
                const [current] = prev
                setBackgroundWindows(bg => bg.includes(current) ? bg : [...bg, current])
                return [conversationId]
            }
        })

        setMinimizedWindows(prev => {
            const newSet = new Set(prev)
            newSet.delete(conversationId)
            return newSet
        })
    }

    const closeChatWindow = (conversationId: number) => {
        setChatWindows(prev => {
            const filtered = prev.filter(id => id !== conversationId)

            // If we have space and background windows, promote one
            if (filtered.length < 1 && backgroundWindows.length > 0) {
                const [nextWindow, ...restBackground] = backgroundWindows
                setBackgroundWindows(restBackground)
                return [...filtered, nextWindow]
            }

            return filtered
        })

        setBackgroundWindows(prev => prev.filter(id => id !== conversationId))
        setMinimizedWindows(prev => {
            const newSet = new Set(prev)
            newSet.delete(conversationId)
            return newSet
        })
    }

    const switchToWindow = (conversationId: number) => {
        // Move window from background to visible
        if (backgroundWindows.includes(conversationId)) {
            setBackgroundWindows(prev => prev.filter(id => id !== conversationId))

            setChatWindows(prev => {
                if (prev.length < 1) {
                    return [...prev, conversationId]
                } else {
                    // Move current window to background
                    const currentWindow = prev[0]
                    setBackgroundWindows(bg => [...bg, currentWindow])
                    return [conversationId]
                }
            })
        }
    }

    const closeBackgroundWindow = (conversationId: number) => {
        setBackgroundWindows(prev => prev.filter(id => id !== conversationId))
        setMinimizedWindows(prev => {
            const newSet = new Set(prev)
            newSet.delete(conversationId)
            return newSet
        })
    }

    const toggleMinimize = (conversationId: number) => {
        setMinimizedWindows(prev => {
            const newSet = new Set(prev)
            if (newSet.has(conversationId)) {
                newSet.delete(conversationId)
            } else {
                newSet.add(conversationId)
            }
            return newSet
        })
    }

    const clearChatState = () => {
        setChatWindows([])
        setBackgroundWindows([])
        setMinimizedWindows(new Set())
        localStorage.removeItem(STORAGE_KEY)
    }

    return (
        <ChatContext.Provider
            value={{
                isChatOpen,
                setChatOpen,
                activeConversationId,
                setActiveConversationId,
                chatWindows,
                backgroundWindows,
                openChatWindow,
                closeChatWindow,
                closeBackgroundWindow,
                switchToWindow,
                minimizedWindows,
                toggleMinimize,
                clearChatState,
                currentUserId,
                // Chat data and functions from central hook
                conversations: chatHook.conversations,
                activeConversation: chatHook.activeConversation,
                loading: chatHook.loading,
                sending: chatHook.sending,
                loadConversations: chatHook.loadConversations,
                loadConversation: chatHook.loadConversation,
                sendMessage: chatHook.sendMessage,
                createConversation: chatHook.createConversation,
                markMessagesAsRead: chatHook.markMessagesAsRead,
                setActiveConversation: chatHook.setActiveConversation,
            }}
        >
            {children}
        </ChatContext.Provider>
    )
}