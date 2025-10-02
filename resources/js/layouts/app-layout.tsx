import { ChatContainer } from '@/components/chat/ChatContainer';
import { OnlineUsers } from '@/components/chat/OnlineUsers';
import { OnboardingWizard } from '@/components/onboarding/OnboardingWizard';
import { Toaster } from '@/components/ui/toaster';
import { ChatProvider } from '@/contexts/ChatContext';
import { useNotificationManager } from '@/hooks/useNotificationManager';
import { useOnboarding } from '@/hooks/useOnboarding';
import { usePresenceManager } from '@/hooks/usePresenceManager';
import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import { type BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/react';
import { type ReactNode, useMemo } from 'react';

interface AppLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
}

export default ({ children, breadcrumbs, ...props }: AppLayoutProps) => {
    const { auth } = usePage<{ auth: { user?: { id: number } } }>().props;

    // Stabilize currentUserId to prevent unnecessary re-renders
    const currentUserId = useMemo(() => auth.user?.id, [auth.user?.id]);

    // Manage presence globally
    usePresenceManager({ currentUserId });

    // Manage notifications globally
    useNotificationManager({ currentUserId });

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
