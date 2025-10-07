import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogTitle } from '@/components/ui/dialog';
import { ShieldCheckIcon } from 'lucide-react';
import { AiOutlineClose } from 'react-icons/ai';

export function UnblockConfirmationDialog(props: {
    state: { unfollowDialogOpen: boolean; blockDialogOpen: boolean; unblockDialogOpen: boolean };
    onOpenChange: (open: boolean) => void;
    name: string;
    disabled: boolean;
    onClick: () => void;
}) {
    return (
        <Dialog open={props.state.unblockDialogOpen} onOpenChange={props.onOpenChange}>
            <DialogContent className="p-6">
                <DialogTitle>
                    Desbloquear <b>{props.name}</b>?
                </DialogTitle>
                <DialogDescription className="pt-4">
                    <span className="text-muted-foreground text-sm">
                        Ao desbloquear este utilizador, ele poderá novamente:
                        <ul className="mt-2 list-disc pl-5">
                            <li>Ver o seu perfil</li>
                            <li>Enviar-lhe mensagens</li>
                            <li>Comentar nos seus hunts</li>
                            <li>Segui-lo</li>
                        </ul>
                    </span>
                </DialogDescription>
                <DialogFooter className="pt-4">
                    <DialogClose asChild>
                        <Button variant="secondary">
                            <AiOutlineClose />
                            Cancelar
                        </Button>
                    </DialogClose>
                    <Button variant="default" disabled={props.disabled} onClick={props.onClick}>
                        <ShieldCheckIcon /> Desbloquear
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
