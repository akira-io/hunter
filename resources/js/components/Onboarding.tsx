import PublicProfileController from '@/actions/App/Http/Controllers/PublicProfileController';
import { FollowButton } from '@/components/followable/FollowButton';
import UnfollowButton from '@/components/followable/UnfollowButton';
import { HighlightedSkills } from '@/components/profile/HighlightedSkills';
import { Avatar, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardTitle } from '@/components/ui/card';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useSanitizeExternalUrl, useSanitizeImageUrl } from '@/hooks/use-sanitize-image-url';
import { useStartConversation } from '@/hooks/use-start-conversation';
import { useToast } from '@/hooks/use-toast';
import { useTruncate } from '@/hooks/use-truncate-text';
import { cn } from '@/lib/utils';
import followable from '@/routes/followable';
import privacy from '@/routes/privacy';
import { SharedData, User } from '@/types';
import { useForm, usePage } from '@inertiajs/react';
import { RiBlueskyFill, RiGithubFill, RiLinkedinBoxFill, RiTwitterXFill, RiYoutubeFill } from '@remixicon/react';
import { format } from 'date-fns';
import {
    ArrowLeftIcon,
    ArrowRightIcon,
    BanIcon,
    CheckCircle,
    EllipsisVerticalIcon,
    Globe,
    GraduationCap,
    InfoIcon,
    MessageCircle,
    ShieldCheckIcon,
    UserIcon,
    UserMinusIcon,
    UserPlusIcon,
    XCircle,
} from 'lucide-react';
import * as React from 'react';
import { useState } from 'react';
import { AiOutlineClose } from 'react-icons/ai';
import AvatarGenerator, { genConfig } from 'react-nice-avatar';

interface OnboardingProps extends React.ComponentProps<'div'> {
    user: User;
    hasFollowed?: boolean;
}

interface OnboardingAvatarProps {
    avatarUrl: string | undefined;
    size?: number;
    onClick?: () => void;
}

export function OnboardingAvatar({ avatarUrl, onClick, size = 16 }: OnboardingAvatarProps) {
    const avatarSize = size * 4;
    const config = genConfig({ sex: 'man', hairStyle: 'thick' });
    const sanitizedAvatarUrl = useSanitizeImageUrl(avatarUrl);

    return (
        <Avatar style={{ height: `${avatarSize}px`, width: `${avatarSize}px` }} className="shadow" onClick={onClick}>
            {sanitizedAvatarUrl ? (
                <AvatarImage
                    src={sanitizedAvatarUrl}
                    alt={sanitizedAvatarUrl}
                    className="rounded-full object-cover"
                    style={{ height: `${avatarSize}px`, width: `${avatarSize}px` }}
                />
            ) : (
                <AvatarGenerator style={{ width: `${avatarSize}px`, height: `${avatarSize}px` }} {...config} />
            )}
        </Avatar>
    );
}

function OnboardingLink({ url, name, children }: { url: string | undefined; name: string; children: React.ReactNode }) {
    const sanitizedUrl = useSanitizeExternalUrl(url);

    return sanitizedUrl ? (
        <a
            key={name}
            href={sanitizedUrl}
            target="_blank"
            rel="noopener noreferrer"
            className="text-muted-foreground hover:text-primary"
            aria-label={`Abrir ${name} em nova aba`}
        >
            {children}
        </a>
    ) : null;
}

function OnboardingLinks({ links }: { links: { name: string; url: string | undefined; icon: React.ReactNode }[] }) {
    return (
        <div className="flex gap-2">
            {links.map((link) => (
                <OnboardingLink key={link.name} url={link.url} name={link.name}>
                    {link.icon}
                </OnboardingLink>
            ))}
        </div>
    );
}

function OnboardingSkills({ skills }: { skills: User['skills'] }) {
    return (
        <div className="gradient bg-card mt-8 flex w-full flex-col items-start gap-2 space-y-6 rounded-lg p-4">
            <small>Skills</small>
            {skills?.length == 0 && <small className="dark:text-muted text-xs text-gray-300">nenhuma skill definida</small>}
            <div className="-mt-4 flex flex-wrap items-center gap-1 overflow-x-auto [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                {skills && <HighlightedSkills techs={skills} />}
            </div>
        </div>
    );
}

function OnboardingAbout({ about }: { about: string | undefined }) {
    return (
        <div className="gradient bg-card flex w-full flex-col items-start gap-2 space-y-6 rounded-lg p-4">
            <small>Sobre</small>
            {!about && <small className="dark:text-muted -mt-6 text-xs text-gray-300">nenhuma informação disponivel</small>}
            {about && <div className="-mt-4">{about}</div>}
        </div>
    );
}

export default function Onboarding({ user, hasFollowed = false, ...props }: OnboardingProps) {
    const { auth } = usePage<SharedData>().props;
    const [step, setStep] = useState(1);
    const [open, setOpen] = useState(false);
    const [unfollowDialogOpen, setUnfollowDialogOpen] = useState(false);
    const [blockDialogOpen, setBlockDialogOpen] = useState(false);
    const [unblockDialogOpen, setUnblockDialogOpen] = useState(false);
    const { get } = useForm();
    const { startConversation, isStarting } = useStartConversation();
    const { toast } = useToast();

    const { post: postFollow, processing: followProcessing } = useForm({
        user_id: user.id,
    });

    const { post: postUnfollow, processing: unfollowProcessing } = useForm({
        user_id: user.id,
    });

    const { post: postBlock, processing: blockProcessing } = useForm({});
    const { delete: deleteUnblock, processing: unblockProcessing } = useForm({});

    const links = [
        { name: 'GitHub', url: user.github_url, icon: <RiGithubFill /> },
        { name: 'Twitter', url: user.twitter_url, icon: <RiTwitterXFill /> },
        { name: 'YouTube', url: user.youtube_url, icon: <RiYoutubeFill /> },
        { name: 'LinkedIn', url: user.linkedin_url, icon: <RiLinkedinBoxFill /> },
        { name: 'Bluesky', url: user.bluesky_url, icon: <RiBlueskyFill /> },
        { name: 'Website', url: user.website_url, icon: <Globe /> },
    ];

    const { truncate } = useTruncate();

    const totalSteps = 4;

    const handleContinue = () => {
        if (step < totalSteps) setStep(step + 1);
    };

    const handleBack = () => {
        if (step > 1) setStep(step - 1);
    };

    const handleCardClick = () => {
        setStep(1);
        setOpen(true);
    };

    function nextStepLabel(step: number) {
        switch (step) {
            case 1:
                return 'Formação Acadêmica';
            case 2:
                return 'Exp.Profissional';
            case 3:
                return 'Projetos';
            default:
                return '';
        }
    }

    const has_followed = user.has_followed ?? hasFollowed;
    const isBlocked = user.is_blocked ?? false;

    function gotoProfile() {
        get(PublicProfileController.show({ user: user.id }).url, {
            preserveScroll: true,
            preserveState: true,
        });
    }

    const handleMessageClick = async (e: React.MouseEvent) => {
        e.stopPropagation();
        await startConversation(user.id);
    };

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
            <Card className="relative min-h-40 w-full cursor-pointer overflow-hidden">
                <CardContent className="flex w-full flex-1 items-center justify-center gap-2">
                    <OnboardingAvatar avatarUrl={user.avatar_url} onClick={gotoProfile} />
                    <div className="w-full">
                        <div className="flex items-center justify-between">
                            <CardTitle className="text-xl" onClick={gotoProfile}>
                                {user.name}
                            </CardTitle>
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button
                                        data-pan="onbording-profile"
                                        className="text-muted-forground absolute top-4 right-4 flex h-8 w-8 cursor-pointer border-none shadow-none"
                                        variant="secondary"
                                    >
                                        <EllipsisVerticalIcon className="size-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem onClick={handleCardClick}>
                                        <InfoIcon className="mr-2 size-4" />
                                        Onboarding
                                    </DropdownMenuItem>
                                    <DropdownMenuItem onClick={gotoProfile}>
                                        <UserIcon className="mr-2 size-4" />
                                        Ver Perfil
                                    </DropdownMenuItem>
                                    {auth.user && auth.user.id !== user.id && (
                                        <>
                                            <DropdownMenuSeparator />
                                            {!has_followed && (
                                                <DropdownMenuItem onClick={handleFollow} disabled={followProcessing}>
                                                    <UserPlusIcon className="mr-2 size-4" />
                                                    Seguir
                                                </DropdownMenuItem>
                                            )}
                                            {has_followed && (
                                                <DropdownMenuItem onClick={() => setUnfollowDialogOpen(true)}>
                                                    <UserMinusIcon className="mr-2 size-4" />
                                                    Deixar de Seguir
                                                </DropdownMenuItem>
                                            )}
                                            <DropdownMenuSeparator />
                                            {isBlocked ? (
                                                <DropdownMenuItem
                                                    onClick={() => setUnblockDialogOpen(true)}
                                                    className="text-green-600 dark:text-green-400"
                                                >
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
                        </div>
                        <CardDescription className="text-sm">
                            {user.bio ? truncate(user.bio, 45) : <span className="text-muted">Bio indisponível...</span>}
                        </CardDescription>
                    </div>
                </CardContent>
                {auth.user && auth.user.id !== user.id && (
                    <CardFooter className="flex justify-between gap-2">
                        <Button variant="outline" size="sm" onClick={handleMessageClick} disabled={isStarting} className="flex items-center gap-2">
                            <MessageCircle size={16} />
                            {isStarting ? 'Abrindo...' : 'Mensagem'}
                        </Button>
                        <div className="flex-1" />
                        {!has_followed && <FollowButton user={user} />}
                        {has_followed && <UnfollowButton user={user} />}
                    </CardFooter>
                )}
            </Card>
            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent className="w-ful overflow-auto">
                    <DialogHeader className="bg-card gradient sticky mb-0 w-full items-center justify-between rounded-lg px-4 pb-2 shadow-lg">
                        <div className="flex w-full items-start justify-start pt-4">
                            <OnboardingAvatar avatarUrl={user.avatar_url} />
                            <div className="ml-2 flex flex-col gap-1 text-left">
                                <div>
                                    <DialogTitle className="text-xl">{user.name}</DialogTitle>
                                    <small className="text-xs">{user.email}</small>
                                </div>
                                <OnboardingLinks links={links} />
                            </div>
                        </div>
                    </DialogHeader>
                    <div className="h-100 space-y-6 overflow-y-auto md:px-6">
                        {step === 1 && (
                            <div className="flex shrink-0 flex-col items-start gap-8">
                                <OnboardingSkills skills={user.skills} />
                                <OnboardingAbout about={user.bio} />
                            </div>
                        )}
                        {step === 2 && (
                            <div className="flex w-full shrink-0 flex-col items-start gap-8">
                                <span className="mt-4">Formação Academica</span>
                                <DialogDescription className="w-full space-y-4">
                                    {user.professional_educations?.map((education) => (
                                        <Card className="w-full items-start p-4" key={education.id}>
                                            <CardTitle className="flex w-full items-center gap-1 text-sm font-semibold">
                                                <GraduationCap /> {education.degree}
                                            </CardTitle>
                                            <CardContent className="-mt-2 grid w-full grid-cols-1 items-center gap-4 border-t-1 pt-4 md:grid-cols-2">
                                                <div className="flex flex-col items-start justify-start">
                                                    <small className="text-xs text-gray-500">Instituíção</small>
                                                    <span className="bold text-sm">{education.institution}</span>
                                                </div>
                                                <div className="flex flex-col items-start justify-start">
                                                    <small className="text-xs text-gray-500">Area de Estudo</small>
                                                    <span className="bold text-sm">{education.field_of_study}</span>
                                                </div>
                                                <div className="flex flex-col items-start justify-start">
                                                    <small className="text-xs text-gray-500">Inicio</small>
                                                    <span className="bold text-sm">{format(new Date(education.start_date), 'dd-MM-yyyy')}</span>
                                                </div>
                                                {education.end_date && (
                                                    <div className="flex flex-col items-start justify-start">
                                                        <small className="text-xs text-gray-500">Fim</small>
                                                        <span className="bold text-sm">{format(new Date(education.end_date), 'dd-MM-yyyy')}</span>
                                                    </div>
                                                )}
                                            </CardContent>
                                        </Card>
                                    ))}
                                </DialogDescription>
                            </div>
                        )}
                        {step === 3 && (
                            <>
                                <DialogTitle>Experiência Profissional</DialogTitle>
                                <DialogDescription>Begin building amazing interfaces with our comprehensive component library.</DialogDescription>
                            </>
                        )}
                        {step === 4 && (
                            <>
                                <DialogTitle>Projetos</DialogTitle>
                                <DialogDescription>
                                    Access our extensive documentation and community resources to make the most of Origin UI.
                                </DialogDescription>
                            </>
                        )}
                        <DialogFooter className="fixed right-0 bottom-0 left-0 z-10 flex w-full items-center justify-between border-t p-3">
                            <div className="max:order-1 flex justify-center space-x-1.5">
                                {[1, 2, 3, 4].map((s) => (
                                    <div key={s} className={cn('bg-primary size-1.5 rounded-full', step === s ? 'bg-primary' : 'opacity-20')} />
                                ))}
                            </div>
                            <div className="inline-flex gap-2">
                                {step > 1 && (
                                    <Button className="group" type="button" onClick={handleBack} variant="ghost">
                                        <ArrowLeftIcon size={16} />
                                        Voltar
                                    </Button>
                                )}
                                {step < totalSteps ? (
                                    <Button className="group" type="button" onClick={handleContinue}>
                                        {nextStepLabel(step)}
                                        <ArrowRightIcon size={16} />
                                    </Button>
                                ) : (
                                    <DialogClose asChild>
                                        <Button type="button">Okay</Button>
                                    </DialogClose>
                                )}
                            </div>
                        </DialogFooter>
                    </div>
                </DialogContent>
            </Dialog>

            {/* Unfollow Confirmation Dialog */}
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

            {/* Block Confirmation Dialog */}
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

            {/* Unblock Confirmation Dialog */}
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
