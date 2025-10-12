import type { VisitOptions } from '@inertiajs/core';
import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

interface UseUnsavedChangesGuardOptions {
    enabled?: boolean;
    onConfirm?: () => void;
    onCancel?: () => void;
    message?: string;
}

export function useUnsavedChangesGuard(
    isDirty: boolean,
    options: UseUnsavedChangesGuardOptions = {},
) {
    const {
        enabled = true,
        onConfirm,
        onCancel,
        message = 'Você tem alterações não salvas. Tem certeza que deseja sair?',
    } = options;

    const [showDialog, setShowDialog] = useState(false);
    const [pendingVisit, setPendingVisit] = useState<{
        url: string;
        options: VisitOptions;
    } | null>(null);
    const isNavigatingRef = useRef(false);

    // Handle browser events (refresh, close, back/forward)
    useEffect(() => {
        if (!enabled || !isDirty) {
            return;
        }

        const handleBeforeUnload = (event: BeforeUnloadEvent) => {
            // Modern browsers ignore custom messages and show a default message
            event.preventDefault();
            event.returnValue = '';
            return '';
        };

        window.addEventListener('beforeunload', handleBeforeUnload);

        return () => {
            window.removeEventListener('beforeunload', handleBeforeUnload);
        };
    }, [enabled, isDirty]);

    // Handle Inertia navigation using router hooks
    useEffect(() => {
        if (!enabled || !isDirty) {
            return;
        }

        const unregisterListener = router.on('before', (event) => {
            // If we're already navigating (user confirmed), allow it
            if (isNavigatingRef.current) {
                return true;
            }

            // Ignore prefetch requests (triggered on hover)
            if (event.detail.visit.prefetch) {
                return true;
            }

            // Prevent navigation and show confirmation dialog
            setPendingVisit({
                url: event.detail.visit.url.toString(),
                options: {
                    method: event.detail.visit.method,
                    data: event.detail.visit.data,
                    replace: event.detail.visit.replace,
                    preserveScroll: event.detail.visit.preserveScroll,
                    preserveState: event.detail.visit.preserveState,
                    only: event.detail.visit.only,
                    headers: event.detail.visit.headers,
                    errorBag: event.detail.visit.errorBag,
                    forceFormData: event.detail.visit.forceFormData,
                },
            });
            setShowDialog(true);

            return false; // Cancel navigation
        });

        return () => {
            unregisterListener();
        };
    }, [enabled, isDirty]);

    const handleConfirm = () => {
        if (!pendingVisit) {
            return;
        }

        const visitUrl = pendingVisit.url;
        const visitOptions = pendingVisit.options;

        // Reset state first
        setShowDialog(false);
        setPendingVisit(null);

        // Mark as navigating
        isNavigatingRef.current = true;

        // Call confirm callback
        onConfirm?.();

        // Perform the navigation
        router.visit(visitUrl, {
            ...visitOptions,
            onFinish: () => {
                isNavigatingRef.current = false;
            },
            onError: () => {
                isNavigatingRef.current = false;
            },
        });
    };

    const handleCancel = () => {
        setShowDialog(false);
        setPendingVisit(null);
        isNavigatingRef.current = false;
        onCancel?.();
    };

    return {
        showDialog,
        message,
        handleConfirm,
        handleCancel,
    };
}

export function useFormDirty<T>(data: T, initialData: T): boolean {
    const [isDirty, setIsDirty] = useState(false);

    useEffect(() => {
        setIsDirty(JSON.stringify(data) !== JSON.stringify(initialData));
    }, [data, initialData]);

    return isDirty;
}
