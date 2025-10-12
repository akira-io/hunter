import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { AlertTriangle } from 'lucide-react';

interface UnsavedChangesDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onConfirm: () => void;
    onCancel: () => void;
    title?: string;
    message?: string;
    confirmText?: string;
    cancelText?: string;
}

export function UnsavedChangesDialog({
    open,
    onOpenChange,
    onConfirm,
    onCancel,
    title = 'Alterações não salvas',
    message = 'Você tem alterações não salvas. Se sair agora, essas alterações serão perdidas.',
    confirmText = 'Sair sem salvar',
    cancelText = 'Continuar editando',
}: UnsavedChangesDialogProps) {
    return (
        <AlertDialog open={open} onOpenChange={onOpenChange}>
            <AlertDialogContent className="max-w-md">
                <AlertDialogHeader>
                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/20">
                            <AlertTriangle className="h-5 w-5 text-amber-600 dark:text-amber-500" />
                        </div>
                        <AlertDialogTitle className="text-lg">
                            {title}
                        </AlertDialogTitle>
                    </div>
                    <AlertDialogDescription className="pt-2 text-base">
                        {message}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter className="gap-2 sm:gap-2">
                    <AlertDialogCancel onClick={onCancel} className="sm:flex-1">
                        {cancelText}
                    </AlertDialogCancel>
                    <AlertDialogAction
                        onClick={onConfirm}
                        className="bg-red-600 hover:bg-red-700 sm:flex-1 dark:bg-red-700 dark:hover:bg-red-800"
                    >
                        {confirmText}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}
