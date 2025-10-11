import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { cn } from '@/lib/utils';
import { CheckCircle, Loader2, Trash } from 'lucide-react';
import { AiOutlineClose } from 'react-icons/ai';

interface HunterConfirmDialogProps {
    processing: boolean;
    onConfirm: () => void;
    className?: string;
    title: string;
}

export function HunterConfirmDialog({
    title,
    className,
    onConfirm,
    processing,
}: HunterConfirmDialogProps) {
    return (
        <Dialog>
            <DialogTrigger
                className={cn(
                    'flex cursor-pointer items-center gap-2',
                    className,
                )}
            >
                <Trash size={16} className="opacity-60" aria-hidden="true" />
                Eliminar
            </DialogTrigger>
            <DialogContent className="p-6">
                <DialogTitle>Eliminar {title} </DialogTitle>
                <DialogDescription className="pt-4">
                    <p>Tem certeza de que deseja continuar?</p>
                    <p className="mt-2">
                        Esta ação é <strong>irreversível</strong> e poderá
                        causar a perda permanente de dados.
                    </p>
                </DialogDescription>
                <DialogFooter className="pt-4">
                    <DialogClose asChild>
                        <Button variant="secondary">
                            <AiOutlineClose />
                            Cancelar
                        </Button>
                    </DialogClose>
                    <Button
                        variant="destructive"
                        disabled={processing}
                        onClick={onConfirm}
                    >
                        {processing ? (
                            <Loader2 className="animate-spin" size={16} />
                        ) : (
                            <CheckCircle />
                        )}{' '}
                        Confirmar
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
