export interface User {
    id: number;
    name: string;
    avatar_url?: string;
}

export interface Message {
    id: number;
    content: string;
    type: 'text' | 'image' | 'file';
    metadata?: Record<string, unknown> | null;
    created_at: string;
    user: User;
}

export interface Conversation {
    id: number;
    title: string;
    type: 'direct' | 'group';
    participants: User[];
    last_message?: Message;
    last_message_at?: string;
    unread_count: number;
    messages?: Message[];
    other_participant?: User | null;
}
