import { ChatContainer } from '@/components/chat/ChatContainer';
import { OnlineUsers } from '@/components/chat/OnlineUsers';
import { OnboardingWizard } from '@/components/onboarding/OnboardingWizard';
import { Toaster } from '@/components/ui/toaster';
import { ChatProvider } from '@/contexts/ChatContext';
import { useNotificationManager } from '@/hooks/useNotificationManager';
import { useOnboarding } from '@/hooks/useOnboarding';
import { usePresenceManager } from '@/hooks/usePresenceManager';
import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import { type BreadcrumbItem, type Notification } from '@/types';
import { usePage } from '@inertiajs/react';
import { type ReactNode, useMemo } from 'react';

interface AppLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
}

export default ({ children, breadcrumbs, ...props }: AppLayoutProps) => {
    const page = usePage<{
        auth: { user?: { id: number } };
        notifications?: { data: Notification[] };
        unread_count?: number;
    }>();

    const { auth, notifications, unread_count } = page.props;

    // Stabilize currentUserId to prevent unnecessary re-renders
    const currentUserId = useMemo(() => auth.user?.id, [auth.user?.id]);

    // Manage presence globally
    usePresenceManager({ currentUserId });

    // Manage notifications globally - pass Inertia data if available
    useNotificationManager({
        currentUserId,
        notifications: notifications?.data,
        unreadCount: unread_count,
    });

    // Manage onboarding wizard
    const { showOnboarding, closeOnboarding } = useOnboarding();

    return (
        <ChatProvider currentUserId={currentUserId}>
            <AppLayoutTemplate breadcrumbs={breadcrumbs} {...props}>
                {children}
                <Toaster />
                {auth.user && (
                    <>
                        <ChatContainer currentUserId={currentUserId} />
                        <OnlineUsers currentUserId={currentUserId} />
                        <OnboardingWizard isOpen={showOnboarding} onClose={closeOnboarding} />
                    </>
                )}
            </AppLayoutTemplate>
        </ChatProvider>
    );
};
