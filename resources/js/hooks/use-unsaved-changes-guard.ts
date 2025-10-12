import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

interface UseUnsavedChangesGuardOptions {
    enabled?: boolean;
    onConfirm?: () => void;
    onCancel?: () => void;
    message?: string;
}

/**
 * Hook to prevent users from accidentally leaving a page with unsaved changes.
 * Handles both Inertia navigation and browser events (refresh, close, back/forward).
 *
 * @param isDirty - Whether the form has unsaved changes
 * @param options - Configuration options
 *
 * @example
 * ```tsx
 * const [formData, setFormData] = useState(initialData);
 * const isDirty = !isEqual(formData, initialData);
 *
 * useUnsavedChangesGuard(isDirty, {
 *   enabled: true,
 *   message: 'You have unsaved changes. Are you sure you want to leave?'
 * });
 * ```
 */
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
    const [pendingNavigation, setPendingNavigation] = useState<(() => void) | null>(null);
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

    // Handle Inertia navigation
    useEffect(() => {
        if (!enabled || !isDirty) {
            return;
        }

        const handleStart = (event: CustomEvent) => {
            // If we're already navigating (user confirmed), allow it
            if (isNavigatingRef.current) {
                return;
            }

            // Prevent navigation and show confirmation dialog
            event.preventDefault();

            // Store the navigation callback
            setPendingNavigation(() => () => {
                isNavigatingRef.current = true;
                // Use the visit options from the event
                router.visit(event.detail.visit.url, {
                    ...event.detail.visit,
                    onFinish: () => {
                        isNavigatingRef.current = false;
                    },
                });
            });

            setShowDialog(true);
        };

        // Listen for Inertia navigation start events
        document.addEventListener('inertia:before', handleStart as EventListener);

        return () => {
            document.removeEventListener('inertia:before', handleStart as EventListener);
        };
    }, [enabled, isDirty]);

    const handleConfirm = () => {
        setShowDialog(false);

        if (pendingNavigation) {
            onConfirm?.();
            pendingNavigation();
            setPendingNavigation(null);
        }
    };

    const handleCancel = () => {
        setShowDialog(false);
        setPendingNavigation(null);
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

/**
 * Hook to track if form data has changed from its initial state.
 *
 * @param data - Current form data
 * @param initialData - Initial form data
 * @returns Whether the form is dirty (has changes)
 *
 * @example
 * ```tsx
 * const isDirty = useFormDirty(formData, initialData);
 * ```
 */
export function useFormDirty<T>(data: T, initialData: T): boolean {
    const [isDirty, setIsDirty] = useState(false);

    useEffect(() => {
        setIsDirty(JSON.stringify(data) !== JSON.stringify(initialData));
    }, [data, initialData]);

    return isDirty;
}
