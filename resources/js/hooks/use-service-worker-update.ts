import { useEffect, useState } from 'react';

export function useServiceWorkerUpdate() {
    const [updateAvailable, setUpdateAvailable] = useState(false);
    const [waitingWorker, setWaitingWorker] = useState<ServiceWorker | null>(
        null,
    );

    useEffect(() => {
        if (!('serviceWorker' in navigator)) {
            console.log('[SW Update] Service Worker not supported');
            return;
        }

        if (!import.meta.env.PROD) {
            console.log(
                '[SW Update] Not in production mode, skipping SW registration',
            );
            return;
        }

        const handleUpdate = () => {
            navigator.serviceWorker
                .register('/sw.js')
                .then((registration) => {
                    console.log(
                        '[PWA] Service Worker registered:',
                        registration.scope,
                    );

                    // Check for updates periodically
                    const intervalId = setInterval(() => {
                        registration.update();
                    }, 60000); // Check every minute

                    // Handle service worker updates
                    registration.addEventListener('updatefound', () => {
                        console.log('[SW Update] Update found!');
                        const newWorker = registration.installing;
                        if (newWorker) {
                            newWorker.addEventListener('statechange', () => {
                                console.log(
                                    '[SW Update] New worker state:',
                                    newWorker.state,
                                );
                                if (
                                    newWorker.state === 'installed' &&
                                    navigator.serviceWorker.controller
                                ) {
                                    // New service worker available
                                    console.log(
                                        '[SW Update] New version available!',
                                    );
                                    setWaitingWorker(newWorker);
                                    setUpdateAvailable(true);
                                }
                            });
                        }
                    });

                    return () => clearInterval(intervalId);
                })
                .catch((error) => {
                    console.error(
                        '[PWA] Service Worker registration failed:',
                        error,
                    );
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
            waitingWorker.postMessage({ type: 'SKIP_WAITING' });
            window.location.reload();
        }
    };

    const dismissUpdate = () => {
        setUpdateAvailable(false);
    };

    return {
        updateAvailable,
        applyUpdate,
        dismissUpdate,
    };
}
