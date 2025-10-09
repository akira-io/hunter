import { AppContent } from '@/components/AppContent';
import { AppSidebar } from '@/components/AppSidebar';
import { AppShell } from '@/components/app-shell';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { type BreadcrumbItem } from '@/types';
import { type PropsWithChildren } from 'react';

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
}: PropsWithChildren<{
    breadcrumbs?: BreadcrumbItem[];
}>) {
    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                <div className="mt-5">{children}</div>
            </AppContent>
        </AppShell>
    );
}
