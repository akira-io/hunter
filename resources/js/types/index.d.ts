import { Option } from '@/components/ui/multiselect';
import { LucideIcon } from 'lucide-react';
import { IconType } from 'react-icons';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | null | IconType;
    isActive?: boolean;
    badge?: number;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;

    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar_url?: string;
    bio?: string;
    email_verified_at?: string;
    created_at: string;
    updated_at: string;
    location?: string;
    skills?: Option[];
    website_url?: string;
    github_url?: string;
    twitter_url?: string;
    linkedin_url?: string;
    bluesky_url?: string;
    youtube_url?: string;
    professional_educations?: AcademicBackground[];
    has_followed: boolean;
    is_blocked?: boolean;
    user_name?: string;
    background_image_url: string;

    [key: string]: unknown; // This allows for additional properties...
}

export interface AcademicBackground {
    id: number;
    user_id: number;
    institution: string;
    degree: string;
    start_date: string;
    end_date?: string;
    field_of_study: string;
}

export interface ProfileStoreTypes {
    isOpen: boolean;
    open: () => void;
    close: () => void;
    set: (isOpen: boolean) => void;
}

export interface Hunt {
    id: number;
    content: string;
    created_at: string;
    updated_at: string;
    owner: User;
    image_url?: string;
    image_processing_status?: 'pending' | 'processing' | 'completed' | 'failed';
    comments: Comment[];
    shares: number;
    likes_count: number;
    views: number;
    has_liked: boolean;
    can_comment: boolean;
    is_owner: boolean;
    metrics?: HuntMetrics | null;
}

export interface HuntMetrics {
    // Raw counts
    views: number;
    likes: number;
    comments: number;
    shares: number;
    total_engagements: number;

    // Advanced metrics (only visible to owner)
    engagement_rate: number;
    interaction_rate: number;
    comment_rate: number;
    share_rate: number;
    quality_score: number;
    virality_coefficient: number;
    avg_engagement_per_view: number;
    performance_level: 'poor' | 'below_average' | 'average' | 'good' | 'excellent';
    rank: number; // 1-5 stars
    is_viral: boolean;
    is_performing_well: boolean;
}

export interface Comment {
    id: number;
    content: string;
    created_at: string;
    updated_at: string;
    commenter: User;
    hunt_id: number;
    likes_count: number;
    has_liked: boolean;
}

export interface Notification {
    id: string;
    type: string;
    title: string;
    message: string;
    data: Record<string, unknown>;
    read_at: string | null;
    created_at: string;
    created_at_human: string;
}

export interface PaginationInfo {
    current_page: number;
    per_page: number;
    total: number;
    total_pages: number;
    has_next_page: boolean;
    has_prev_page: boolean;
}

export interface NotificationCounts {
    all: number;
    unread: number;
    read: number;
}
