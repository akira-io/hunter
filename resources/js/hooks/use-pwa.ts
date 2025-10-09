import { useEffect, useState } from 'react';

// Type for BeforeInstallPromptEvent
interface BeforeInstallPromptEvent extends Event {
    prompt: () => Promise<void>;
    userChoice: Promise<{ outcome: 'accepted' | 'dismissed' }>;
}

/**
 * Hook to detect if the app is running as a PWA (Progressive Web App)
 * Returns true if the app is installed and running in standalone mode
 */
export function usePWA() {
    const [isPWA, setIsPWA] = useState(false);

    useEffect(() => {
        const checkPWA = () => {
            // Check if running in standalone mode (iOS)
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches;

            // Check if running as PWA on iOS Safari
            const isIOSStandalone =
                'standalone' in window.navigator &&
                (
                    window.navigator as {
                        standalone?: boolean;
                    }
                ).standalone;

            // Check if running in browser tab mode
            const isInBrowser = window.matchMedia('(display-mode: browser)').matches;

            // PWA is considered installed if running in standalone mode
            const isPWAMode = isStandalone || isIOSStandalone || !isInBrowser;

            setIsPWA(isPWAMode);
        };

        checkPWA();

        // Listen for display mode changes
        const mediaQuery = window.matchMedia('(display-mode: standalone)');
        const handleChange = () => checkPWA();

        mediaQuery.addEventListener('change', handleChange);

        return () => {
            mediaQuery.removeEventListener('change', handleChange);
        };
    }, []);

    return isPWA;
}

/**
 * Hook to check if push notifications are supported and enabled
 */
export function usePushNotifications() {
    const [isSupported, setIsSupported] = useState(false);
    const [permission, setPermission] = useState<NotificationPermission>('default');

    useEffect(() => {
        const checkSupport = async () => {
            // Check if notifications are supported
            const supported = 'Notification' in window && 'serviceWorker' in navigator && 'PushManager' in window;

            setIsSupported(supported);

            if (supported) {
                setPermission(Notification.permission);
            }
        };

        checkSupport();
    }, []);

    const requestPermission = async (): Promise<NotificationPermission> => {
        if (!isSupported) {
            return 'denied';
        }

        try {
            const result = await Notification.requestPermission();
            setPermission(result);
            return result;
        } catch (error) {
            console.error('Error requesting notification permission:', error);
            return 'denied';
        }
    };

    return {
        isSupported,
        permission,
        requestPermission,
        isGranted: permission === 'granted',
        isDenied: permission === 'denied',
        isDefault: permission === 'default',
    };
}

/**
 * Check if app is running in PWA mode
 */
export function isPWAMode(): boolean {
    if (typeof window === 'undefined') return false;

    const isStandalone = window.matchMedia('(display-mode: standalone)').matches;
    const isIOSStandalone = 'standalone' in window.navigator && (window.navigator as { standalone?: boolean }).standalone === true;

    return isStandalone || isIOSStandalone;
}

/**
 * Get install prompt event for PWA installation
 */
export function useInstallPrompt() {
    const [installPrompt, setInstallPrompt] = useState<BeforeInstallPromptEvent | null>(null);
    const [isInstallable, setIsInstallable] = useState(false);

    useEffect(() => {
        const handleBeforeInstallPrompt = (e: Event) => {
            // Prevent the default browser install prompt
            e.preventDefault();
            setInstallPrompt(e as BeforeInstallPromptEvent);
            setIsInstallable(true);
        };

        window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);

        return () => {
            window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
        };
    }, []);

    const promptInstall = async () => {
        if (!installPrompt) {
            return false;
        }

        // Show the install prompt
        installPrompt.prompt();

        // Wait for the user's response
        const result = await installPrompt.userChoice;

        if (result.outcome === 'accepted') {
            console.log('PWA installed');
            setIsInstallable(false);
            setInstallPrompt(null);
            return true;
        }

        return false;
    };

    return {
        isInstallable,
        promptInstall,
    };
}
