import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { RefreshCw, Sparkles } from 'lucide-react';
import { useEffect } from 'react';

interface UpdateAvailableDialogProps {
    open: boolean;
    onUpdate: () => void;
    onLater: () => void;
}

export function UpdateAvailableDialog({ open, onUpdate, onLater }: UpdateAvailableDialogProps) {
    useEffect(() => {
        console.log('[UpdateDialog] Open state changed:', open);
    }, [open]);

    return (
        <Dialog open={open} onOpenChange={(isOpen) => !isOpen && onLater()}>
            <DialogContent className="top-[50%] left-[50%] max-w-md -translate-x-1/2 -translate-y-1/2 rounded-2xl border p-6">
                <DialogHeader className="gap-4">
                    <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-purple-500/10">
                        <Sparkles className="h-6 w-6 text-purple-500" />
                    </div>
                    <DialogTitle>Nova Versão Disponível!</DialogTitle>
                    <DialogDescription>Uma nova versão do Hunter está disponível com melhorias e correções. Atualizar agora?</DialogDescription>
                </DialogHeader>
                <DialogFooter className="gap-2">
                    <Button variant="outline" onClick={onLater}>
                        Mais tarde
                    </Button>
                    <Button onClick={onUpdate} className="gap-2">
                        <RefreshCw className="h-4 w-4" />
                        Atualizar agora
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
