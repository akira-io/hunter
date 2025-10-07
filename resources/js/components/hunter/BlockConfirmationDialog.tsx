import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogTitle } from '@/components/ui/dialog';
import { BanIcon } from 'lucide-react';
import { AiOutlineClose } from 'react-icons/ai';

export function BlockConfirmationDialog(props: {
    state: { unfollowDialogOpen: boolean; blockDialogOpen: boolean; unblockDialogOpen: boolean };
    onOpenChange: (open: boolean) => void;
    name: string;
    disabled: boolean;
    onClick: () => void;
}) {
    return (
        <Dialog open={props.state.blockDialogOpen} onOpenChange={props.onOpenChange}>
            <DialogContent className="p-6">
                <DialogTitle>
                    Bloquear <b>{props.name}</b>?
                </DialogTitle>
                <DialogDescription className="pt-4" asChild>
                    <span className="text-muted-foreground text-sm">
                        Ao bloquear este utilizador, ele não poderá:
                        <ul className="mt-2 list-disc pl-5">
                            <li>Ver o seu perfil</li>
                            <li>Enviar-lhe mensagens</li>
                            <li>Comentar nos seus hunts</li>
                            <li>Segui-lo</li>
                        </ul>
                        <span className="mt-2 block">Você pode desbloqueá-lo a qualquer momento nas configurações de privacidade.</span>
                    </span>
                </DialogDescription>
                <DialogFooter className="pt-4">
                    <DialogClose asChild>
                        <Button variant="secondary">
                            <AiOutlineClose />
                            Cancelar
                        </Button>
                    </DialogClose>
                    <Button variant="destructive" disabled={props.disabled} onClick={props.onClick}>
                        <BanIcon /> Bloquear
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
