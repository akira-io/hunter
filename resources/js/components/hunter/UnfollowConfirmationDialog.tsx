import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
} from '@/components/ui/dialog';
import { CheckCircle } from 'lucide-react';
import { AiOutlineClose } from 'react-icons/ai';

export function UnfollowConfirmationDialog(props: {
    state: {
        unfollowDialogOpen: boolean;
        blockDialogOpen: boolean;
        unblockDialogOpen: boolean;
    };
    onOpenChange: (open: boolean) => void;
    name: string;
    disabled: boolean;
    onClick: () => void;
}) {
    return (
        <Dialog
            open={props.state.unfollowDialogOpen}
            onOpenChange={props.onOpenChange}
        >
            <DialogContent className="p-6">
                <DialogTitle>
                    Deixar de Seguir <b>{props.name}</b>?
                </DialogTitle>
                <DialogDescription className="pt-4">
                    <span className="text-sm text-muted-foreground">
                        Você pode voltar a segui-lo a qualquer momento.
                    </span>
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
                        disabled={props.disabled}
                        onClick={props.onClick}
                    >
                        <CheckCircle /> Confirmar
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
