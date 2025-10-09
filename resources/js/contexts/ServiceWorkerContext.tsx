import { UpdateAvailableDialog } from '@/components/UpdateAvailableDialog';
import { createContext, ReactNode, useContext, useEffect, useState } from 'react';

interface ServiceWorkerContextType {
    updateAvailable: boolean;
}

const ServiceWorkerContext = createContext<ServiceWorkerContextType>({ updateAvailable: false });

export function ServiceWorkerProvider({ children }: { children: ReactNode }) {
    const [updateAvailable, setUpdateAvailable] = useState(false);
    const [waitingWorker, setWaitingWorker] = useState<ServiceWorker | null>(null);

    useEffect(() => {
        const handleUpdate = () => {
            console.log('[SW] Starting Service Worker registration...');

            navigator.serviceWorker
                .register('/sw.js')
                .then((registration) => {
                    console.log('[SW] Service Worker registered:', registration.scope);
                    console.log('[SW] Current state - installing:', registration.installing);
                    console.log('[SW] Current state - waiting:', registration.waiting);
                    console.log('[SW] Current state - active:', registration.active);

                    // If there's already a waiting worker, show update dialog immediately
                    if (registration.waiting) {
                        console.log('[SW] Update already waiting!');
                        setWaitingWorker(registration.waiting);
                        setUpdateAvailable(true);
                    }

                    // Check for updates periodically
                    const intervalId = setInterval(() => {
                        console.log('[SW] Checking for updates...');
                        registration.update();
                    }, 60000); // Check every minute

                    // Handle service worker updates
                    registration.addEventListener('updatefound', () => {
                        console.log('[SW] ⚡ Update found!');
                        const newWorker = registration.installing;
                        console.log('[SW] New worker:', newWorker);

                        if (newWorker) {
                            newWorker.addEventListener('statechange', () => {
                                console.log('[SW] 🔄 New worker state changed to:', newWorker.state);
                                console.log('[SW] Has controller?', !!navigator.serviceWorker.controller);

                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    console.log('[SW] ✅ New version available! Showing dialog...');
                                    setWaitingWorker(newWorker);
                                    setUpdateAvailable(true);
                                }
                            });
                        }
                    });

                    return () => clearInterval(intervalId);
                })
                .catch((error) => {
                    console.error('[SW] ❌ Service Worker registration failed:', error);
                });

            // Handle service worker controller change (this triggers auto-reload)
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                console.log('[SW] 🔄 Controller changed - reloading page...');
                window.location.reload();
            });
        };

        if (document.readyState === 'complete') {
            handleUpdate();
        } else {
            window.addEventListener('load', handleUpdate);
            return () => window.removeEventListener('load', handleUpdate);
        }
    }, []);

    const applyUpdate = () => {
        if (waitingWorker) {
            console.log('[SW] Applying update...');
            waitingWorker.postMessage({ type: 'SKIP_WAITING' });
            window.location.reload();
        }
    };

    const dismissUpdate = () => {
        console.log('[SW] Update dismissed');
        setUpdateAvailable(false);
    };

    return (
        <ServiceWorkerContext.Provider value={{ updateAvailable }}>
            {children}
            <UpdateAvailableDialog open={updateAvailable} onUpdate={applyUpdate} onLater={dismissUpdate} />
        </ServiceWorkerContext.Provider>
    );
}

export function useServiceWorker() {
    return useContext(ServiceWorkerContext);
}
