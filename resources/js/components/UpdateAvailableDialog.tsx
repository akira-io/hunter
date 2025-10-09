import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { RefreshCw, Sparkles } from 'lucide-react';

interface UpdateAvailableDialogProps {
    open: boolean;
    onUpdate: () => void;
    onLater: () => void;
}

export function UpdateAvailableDialog({ open, onUpdate, onLater }: UpdateAvailableDialogProps) {
    return (
        <Dialog open={open} onOpenChange={(isOpen) => !isOpen && onLater()}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-950">
                        <Sparkles className="h-6 w-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <DialogTitle className="text-center">Nova Versão Disponível!</DialogTitle>
                    <DialogDescription className="text-center">
                        Uma nova versão do Hunter está disponível com melhorias e correções. Atualizar agora?
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter className="flex-col gap-2 sm:flex-row sm:justify-center">
                    <Button variant="outline" onClick={onLater} className="w-full sm:w-auto">
                        Mais tarde
                    </Button>
                    <Button
                        onClick={onUpdate}
                        className="w-full gap-2 bg-gradient-to-r from-purple-500 to-purple-700 hover:from-purple-600 hover:to-purple-800 sm:w-auto"
                    >
                        <RefreshCw className="h-4 w-4" />
                        Atualizar agora
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
