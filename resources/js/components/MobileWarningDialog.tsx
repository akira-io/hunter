import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { AlertTriangle } from 'lucide-react';
import { useEffect, useState } from 'react';

export function MobileWarningDialog() {
    const [open, setOpen] = useState(false);

    useEffect(() => {
        const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
        const hasSeenWarning = sessionStorage.getItem('mobile-warning-seen');

        if (isMobile && !hasSeenWarning) {
            setOpen(true);
        }
    }, []);

    const handleClose = () => {
        sessionStorage.setItem('mobile-warning-seen', 'true');
        setOpen(false);
    };

    return (
        <Dialog open={open} onOpenChange={handleClose}>
            <DialogContent className="top-[50%] left-[50%] max-w-sm -translate-x-1/2 -translate-y-1/2 rounded-2xl border p-6 px-4">
                <DialogHeader className="gap-4">
                    <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-orange-500/10">
                        <AlertTriangle className="h-6 w-6 text-orange-500" />
                    </div>
                    <DialogTitle>Atenção</DialogTitle>
                    <DialogDescription className="text-center">
                        Esta aplicação ainda não está totalmente otimizada para dispositivos móveis. Você pode encontrar alguns bugs ou problemas de
                        usabilidade.
                        <br />
                        <br />
                        Para a melhor experiência, recomendamos usar um computador desktop.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter className="gap-2">
                    <Button onClick={handleClose} className="w-full">
                        Entendi
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
