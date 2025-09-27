import { OnlineUsers } from '@/components/chat/OnlineUsers';
import { Toaster } from '@/components/ui/toaster';
import { ChatProvider } from '@/contexts/ChatContext';
import { usePresenceManager } from '@/hooks/usePresenceManager';
import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import { type BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/react';
import { type ReactNode } from 'react';
import { ChatContainer } from '@/components/chat/ChatContainer';

interface AppLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
}

export default ({ children, breadcrumbs, ...props }: AppLayoutProps) => {
    const { auth } = usePage<{ auth: { user?: { id: number } } }>().props;

    // Manage presence globally
    usePresenceManager({ currentUserId: auth.user?.id });

    return (
        <ChatProvider currentUserId={auth.user?.id}>
            <AppLayoutTemplate breadcrumbs={breadcrumbs} {...props}>
                {children}
                <Toaster />
                {auth.user && (
                    <>
                        <ChatContainer currentUserId={auth.user.id} />
                        <OnlineUsers currentUserId={auth.user.id} />
                    </>
                )}
            </AppLayoutTemplate>
        </ChatProvider>
    );
};
