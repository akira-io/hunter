import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
} from '@/components/ui/dialog';
import { AlertTriangle } from 'lucide-react';
import { type ReactNode } from 'react';
import { AiOutlineClose } from 'react-icons/ai';

interface HunterConfirmationDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onConfirm: () => void;
    onCancel: () => void;
    title?: string;
    message?: string;
    confirmText?: string;
    cancelText?: string;
    icon?: ReactNode;
    variant?: 'warning' | 'danger' | 'info';
}

export function HunterConfirmationDialog({
    open,
    onOpenChange,
    onConfirm,
    onCancel,
    title = 'Alterações não salvas',
    message = 'Você tem alterações não salvas. Se sair agora, essas alterações serão perdidas.',
    confirmText = 'Sair sem salvar',
    cancelText = 'Continuar editando',
    icon,
    variant = 'warning',
}: HunterConfirmationDialogProps) {
    const variantStyles = {
        warning: {
            bg: 'bg-amber-100 dark:bg-amber-900/20',
            iconColor: 'text-amber-600 dark:text-amber-500',
            buttonVariant: 'destructive' as const,
        },
        danger: {
            bg: 'bg-red-100 dark:bg-red-900/20',
            iconColor: 'text-red-600 dark:text-red-500',
            buttonVariant: 'destructive' as const,
        },
        info: {
            bg: 'bg-blue-100 dark:bg-blue-900/20',
            iconColor: 'text-blue-600 dark:text-blue-500',
            buttonVariant: 'default' as const,
        },
    };

    const styles = variantStyles[variant];
    const IconComponent = icon || <AlertTriangle className="h-6 w-6" />;

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="p-6">
                <div className="flex flex-col items-center text-center">
                    <div
                        className={`mb-4 flex h-12 w-12 items-center justify-center rounded-full ${styles.bg}`}
                    >
                        <div className={styles.iconColor}>{IconComponent}</div>
                    </div>
                    <DialogTitle className="mb-2">{title}</DialogTitle>
                    <DialogDescription>
                        <span className="text-sm text-muted-foreground">
                            {message}
                        </span>
                    </DialogDescription>
                </div>
                <DialogFooter className="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-center">
                    <Button
                        variant="secondary"
                        onClick={onCancel}
                        className="w-full sm:max-w-[180px] sm:flex-1"
                    >
                        <AiOutlineClose />
                        {cancelText}
                    </Button>
                    <Button
                        variant={styles.buttonVariant}
                        onClick={onConfirm}
                        className="w-full sm:max-w-[180px] sm:flex-1"
                    >
                        {icon || <AlertTriangle className="h-4 w-4" />}
                        {confirmText}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
