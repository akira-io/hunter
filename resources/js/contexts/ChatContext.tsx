import React, { createContext, useContext, useState } from 'react'

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
    currentUserId?: number
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

export const ChatProvider: React.FC<ChatProviderProps> = ({ children, currentUserId }) => {
    const [isChatOpen, setChatOpen] = useState(false)
    const [activeConversationId, setActiveConversationId] = useState<number | null>(null)
    const [chatWindows, setChatWindows] = useState<number[]>([])
    const [backgroundWindows, setBackgroundWindows] = useState<number[]>([])
    const [minimizedWindows, setMinimizedWindows] = useState<Set<number>>(new Set())

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
                currentUserId,
            }}
        >
            {children}
        </ChatContext.Provider>
    )
}