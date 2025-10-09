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
            navigator.serviceWorker
                .register('/sw.js')
                .then((registration) => {
                    console.log('[SW] Service Worker registered:', registration.scope);

                    // Check for updates periodically
                    const intervalId = setInterval(() => {
                        registration.update();
                    }, 60000); // Check every minute

                    // Handle service worker updates
                    registration.addEventListener('updatefound', () => {
                        console.log('[SW] Update found!');
                        const newWorker = registration.installing;
                        if (newWorker) {
                            newWorker.addEventListener('statechange', () => {
                                console.log('[SW] New worker state:', newWorker.state);
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    console.log('[SW] New version available!');
                                    setWaitingWorker(newWorker);
                                    setUpdateAvailable(true);
                                }
                            });
                        }
                    });

                    return () => clearInterval(intervalId);
                })
                .catch((error) => {
                    console.error('[SW] Service Worker registration failed:', error);
                });

            // Handle service worker controller change
            navigator.serviceWorker.addEventListener('controllerchange', () => {
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
