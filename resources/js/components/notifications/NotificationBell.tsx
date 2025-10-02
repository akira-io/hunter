import { useUnreadCount } from '@/stores/notificationStore';
import { Bell } from 'lucide-react';
import React, { useState } from 'react';
import { NotificationDropdown } from './NotificationDropdown';
import { router } from '@inertiajs/react';

interface NotificationBellProps {
    currentUserId?: number;
}

export const NotificationBell: React.FC<NotificationBellProps> = ({ currentUserId }) => {
    const [isOpen, setIsOpen] = useState(false);
    const unreadCount = useUnreadCount();

    const handleToggle = () => {
        // Check if it's mobile (screen width < 640px which is Tailwind's 'sm' breakpoint)
        const isMobile = window.innerWidth < 640;

        if (isMobile) {
            // On mobile, navigate directly to notifications page
            router.visit('/notifications');
        } else {
            // On desktop, toggle dropdown
            setIsOpen(!isOpen);
        }
    };

    return (
        <div className="relative">
            <button
                onClick={handleToggle}
                className={`relative cursor-pointer touch-manipulation rounded-lg p-2 transition-all duration-200 ${
                    isOpen ? 'bg-accent text-accent-foreground' : 'text-muted-foreground hover:text-foreground hover:bg-accent/50'
                }`}
                data-testid="notification-bell"
                aria-label={`Notificações${unreadCount > 0 ? ` (${unreadCount} não lidas)` : ''}`}
                aria-expanded={isOpen}
            >
                <Bell size={20} className={`transition-transform duration-200 ${isOpen ? 'scale-110' : ''}`} />
                {unreadCount > 0 && (
                    <span
                        className="ring-background absolute -top-0.5 -right-0.5 flex h-5 w-5 min-w-[20px] animate-pulse items-center justify-center rounded-full bg-red-500 text-xs font-semibold text-white shadow-sm ring-2"
                        data-testid="notification-badge"
                    >
                        {unreadCount > 99 ? '99+' : unreadCount}
                    </span>
                )}
            </button>

            {isOpen && <NotificationDropdown isOpen={isOpen} onClose={() => setIsOpen(false)} />}
        </div>
    );
};
