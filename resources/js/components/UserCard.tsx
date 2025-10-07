import PublicProfileController from '@/actions/App/Http/Controllers/PublicProfileController';
import { SocialDropdownMenu } from '@/components/hunter/SocialDropdownMenu';
import { Avatar, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogTitle } from '@/components/ui/dialog';
import { useSanitizeImageUrl } from '@/hooks/use-sanitize-image-url';
import { useSocialActions } from '@/hooks/use-social-actions';
import { User } from '@/types';
import { router } from '@inertiajs/react';
import { BanIcon, CheckCircle, ShieldCheckIcon } from 'lucide-react';
import * as React from 'react';
import { AiOutlineClose } from 'react-icons/ai';
import AvatarGenerator, { genConfig } from 'react-nice-avatar';

interface UserCardProps extends React.ComponentProps<'div'> {
    user: User;
}

export default function UserCard({ user, ...props }: UserCardProps) {
    const sanitizedAvatarUrl = useSanitizeImageUrl(user.avatar_url);
    const config = genConfig({ sex: 'man', hairStyle: 'thick' });
    const { processing, handleBlock, handleUnfollow, handleUnblock, state, setState } = useSocialActions(user);

    function gotoProfile() {
        router.get(PublicProfileController.show({ user: user.id }).url, undefined, {
            preserveScroll: true,
            preserveState: true,
        });
    }

    return (
        <div {...props}>
            <Card className="gradient w-full transition-all hover:shadow-lg">
                <CardContent className="flex items-center gap-2">
                    <Avatar style={{ height: '32px', width: '32px' }} className="cursor-pointer shadow" onClick={gotoProfile}>
                        {sanitizedAvatarUrl ? (
                            <AvatarImage
                                src={sanitizedAvatarUrl}
                                alt={user.name}
                                className="rounded-full object-cover"
                                style={{ height: '32px', width: '32px' }}
                            />
                        ) : (
                            <AvatarGenerator style={{ width: '32px', height: '32px' }} {...config} />
                        )}
                    </Avatar>
                    <div className="min-w-0 flex-1 cursor-pointer" onClick={gotoProfile}>
                        <p className="text-md truncate font-medium">{user.name}</p>
                    </div>
                    <SocialDropdownMenu user={user} />
                </CardContent>
            </Card>

            <Dialog open={state.unfollowDialogOpen} onOpenChange={(open) => setState({ ...state, unfollowDialogOpen: open })}>
                <DialogContent className="p-6">
                    <DialogTitle>
                        Deixar de Seguir <b>{user.name}</b>?
                    </DialogTitle>
                    <DialogDescription className="pt-4">
                        <span className="text-muted-foreground text-sm">Você pode voltar a segui-lo a qualquer momento.</span>
                    </DialogDescription>
                    <DialogFooter className="pt-4">
                        <DialogClose asChild>
                            <Button variant="secondary">
                                <AiOutlineClose />
                                Cancelar
                            </Button>
                        </DialogClose>
                        <Button variant="destructive" disabled={processing} onClick={handleUnfollow}>
                            <CheckCircle /> Confirmar
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <Dialog open={state.blockDialogOpen} onOpenChange={(open) => setState({ ...state, blockDialogOpen: open })}>
                <DialogContent className="p-6">
                    <DialogTitle>
                        Bloquear <b>{user.name}</b>?
                    </DialogTitle>
                    <DialogDescription className="pt-4">
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
                        <Button variant="destructive" disabled={processing} onClick={handleBlock}>
                            <BanIcon /> Bloquear
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <Dialog open={state.unblockDialogOpen} onOpenChange={(open) => setState({ ...state, unblockDialogOpen: open })}>
                <DialogContent className="p-6">
                    <DialogTitle>
                        Desbloquear <b>{user.name}</b>?
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
                        <Button variant="default" disabled={processing} onClick={handleUnblock}>
                            <ShieldCheckIcon /> Desbloquear
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
}
