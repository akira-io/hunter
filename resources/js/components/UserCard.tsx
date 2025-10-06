import PublicProfileController from '@/actions/App/Http/Controllers/PublicProfileController';
import { Avatar, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useSanitizeImageUrl } from '@/hooks/use-sanitize-image-url';
import { useToast } from '@/hooks/use-toast';
import followable from '@/routes/followable';
import privacy from '@/routes/privacy';
import { SharedData, User } from '@/types';
import { router, useForm, usePage } from '@inertiajs/react';
import { BanIcon, CheckCircle, EllipsisVerticalIcon, ShieldCheckIcon, UserIcon, UserMinusIcon, UserPlusIcon, XCircle } from 'lucide-react';
import * as React from 'react';
import { useState } from 'react';
import { AiOutlineClose } from 'react-icons/ai';
import AvatarGenerator, { genConfig } from 'react-nice-avatar';

interface UserCardProps extends React.ComponentProps<'div'> {
    user: User;
}

export default function UserCard({ user, ...props }: UserCardProps) {
    const { auth } = usePage<SharedData>().props;
    const sanitizedAvatarUrl = useSanitizeImageUrl(user.avatar_url);
    const config = genConfig({ sex: 'man', hairStyle: 'thick' });
    const { toast } = useToast();
    const [unfollowDialogOpen, setUnfollowDialogOpen] = useState(false);
    const [blockDialogOpen, setBlockDialogOpen] = useState(false);
    const [unblockDialogOpen, setUnblockDialogOpen] = useState(false);

    const { post: postFollow, processing: followProcessing } = useForm({
        user_id: user.id,
    });

    const { post: postUnfollow, processing: unfollowProcessing } = useForm({
        user_id: user.id,
    });

    const { post: postBlock, processing: blockProcessing } = useForm({});
    const { delete: deleteUnblock, processing: unblockProcessing } = useForm({});

    const isFollowing = user.has_followed ?? false;
    const isBlocked = user.is_blocked ?? false;
    const isOwnProfile = auth.user?.id === user.id;

    function gotoProfile() {
        router.get(PublicProfileController.show({ user: user.id }).url, undefined, {
            preserveScroll: true,
            preserveState: true,
        });
    }

    function handleFollow() {
        postFollow(followable.follow().url, {
            preserveScroll: true,
            onSuccess: () => {
                toast({
                    description: `Você começou a seguir ${user.name}`,
                });
            },
            onError: () => {
                toast({
                    variant: 'destructive',
                    description: `Erro ao seguir ${user.name}`,
                });
            },
        });
    }

    function handleUnfollow() {
        postUnfollow(followable.unfollow().url, {
            preserveScroll: true,
            onSuccess: () => {
                toast({
                    description: `Você deixou de seguir ${user.name}`,
                });
                setUnfollowDialogOpen(false);
            },
            onError: () => {
                toast({
                    variant: 'destructive',
                    description: `Erro ao deixar de seguir ${user.name}`,
                });
            },
        });
    }

    function handleBlock() {
        postBlock(privacy.blockUser(user.id).url, {
            preserveScroll: true,
            onSuccess: () => {
                toast({
                    icon: <CheckCircle className="text-green-400" />,
                    title: 'Utilizador bloqueado',
                    description: `${user.name} foi bloqueado com sucesso.`,
                });
                setBlockDialogOpen(false);
            },
            onError: () => {
                toast({
                    variant: 'destructive',
                    icon: <XCircle className="text-red-400" />,
                    title: 'Erro',
                    description: `Erro ao bloquear ${user.name}. Tente novamente.`,
                });
            },
        });
    }

    function handleUnblock() {
        deleteUnblock(privacy.unblockUser(user.id).url, {
            preserveScroll: true,
            onSuccess: () => {
                toast({
                    icon: <CheckCircle className="text-green-400" />,
                    title: 'Utilizador desbloqueado',
                    description: `${user.name} foi desbloqueado com sucesso.`,
                });
                setUnblockDialogOpen(false);
            },
            onError: () => {
                toast({
                    variant: 'destructive',
                    icon: <XCircle className="text-red-400" />,
                    title: 'Erro',
                    description: `Erro ao desbloquear ${user.name}. Tente novamente.`,
                });
            },
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
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button
                                data-pan="user-card-menu"
                                className="text-muted-forground flex size-7 shrink-0 cursor-pointer border-none shadow-none"
                                variant="secondary"
                            >
                                <EllipsisVerticalIcon className="size-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem onClick={gotoProfile}>
                                <UserIcon className="mr-2 size-4" />
                                Ver Perfil
                            </DropdownMenuItem>
                            {!isOwnProfile && (
                                <>
                                    <DropdownMenuSeparator />
                                    {isFollowing ? (
                                        <DropdownMenuItem onClick={() => setUnfollowDialogOpen(true)}>
                                            <UserMinusIcon className="mr-2 size-4" />
                                            Deixar de Seguir
                                        </DropdownMenuItem>
                                    ) : (
                                        <DropdownMenuItem onClick={handleFollow} disabled={followProcessing}>
                                            <UserPlusIcon className="mr-2 size-4" />
                                            Seguir
                                        </DropdownMenuItem>
                                    )}
                                    <DropdownMenuSeparator />
                                    {isBlocked ? (
                                        <DropdownMenuItem onClick={() => setUnblockDialogOpen(true)} className="text-green-600 dark:text-green-400">
                                            <ShieldCheckIcon className="mr-2 size-4" />
                                            Desbloquear
                                        </DropdownMenuItem>
                                    ) : (
                                        <DropdownMenuItem onClick={() => setBlockDialogOpen(true)} className="text-red-600 dark:text-red-400">
                                            <BanIcon className="mr-2 size-4" />
                                            Bloquear
                                        </DropdownMenuItem>
                                    )}
                                </>
                            )}
                        </DropdownMenuContent>
                    </DropdownMenu>
                </CardContent>
            </Card>

            <Dialog open={unfollowDialogOpen} onOpenChange={setUnfollowDialogOpen}>
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
                        <Button variant="destructive" disabled={unfollowProcessing} onClick={handleUnfollow}>
                            <CheckCircle /> Confirmar
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <Dialog open={blockDialogOpen} onOpenChange={setBlockDialogOpen}>
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
                        <Button variant="destructive" disabled={blockProcessing} onClick={handleBlock}>
                            <BanIcon /> Bloquear
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <Dialog open={unblockDialogOpen} onOpenChange={setUnblockDialogOpen}>
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
                        <Button variant="default" disabled={unblockProcessing} onClick={handleUnblock}>
                            <ShieldCheckIcon /> Desbloquear
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
}
