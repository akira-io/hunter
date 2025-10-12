import { UnsavedChangesDialog } from '@/components/unsaved-changes-dialog';
import { useFormDirty, useUnsavedChangesGuard } from '@/hooks/use-unsaved-changes-guard';
import { ReactNode } from 'react';

interface UnsavedChangesGuardProps<T> {
    data: T;
    initialData: T;
    enabled?: boolean;
    children?: ReactNode;
    onConfirm?: () => void;
    onCancel?: () => void;
    title?: string;
    message?: string;
    confirmText?: string;
    cancelText?: string;
}

export function UnsavedChangesGuard<T>({
    data,
    initialData,
    enabled = true,
    children,
    onConfirm,
    onCancel,
    title,
    message,
    confirmText,
    cancelText,
}: UnsavedChangesGuardProps<T>) {
    const isDirty = useFormDirty(data, initialData);

    const {
        showDialog,
        handleConfirm,
        handleCancel,
        message: defaultMessage,
    } = useUnsavedChangesGuard(isDirty, {
        enabled,
        onConfirm,
        onCancel,
        message,
    });

    return (
        <>
            {children}
            <UnsavedChangesDialog
                open={showDialog}
                onOpenChange={(open) => {
                    if (!open) {
                        handleCancel();
                    }
                }}
                onConfirm={handleConfirm}
                onCancel={handleCancel}
                title={title}
                message={message || defaultMessage}
                confirmText={confirmText}
                cancelText={cancelText}
            />
        </>
    );
}
