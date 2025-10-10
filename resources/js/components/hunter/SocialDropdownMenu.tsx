import { BlockConfirmationDialog } from '@/components/hunter/BlockConfirmationDialog';
import { UnblockConfirmationDialog } from '@/components/hunter/UnblockConfirmationDialog';
import { UnfollowConfirmationDialog } from '@/components/hunter/UnfollowConfirmationDialog';
import { OnboardingAvatar } from '@/components/Onboarding';
import { HighlightedSkills } from '@/components/profile/HighlightedSkills';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useProfile } from '@/hooks/use-profile';
import { useSanitizeExternalUrl } from '@/hooks/use-sanitize-image-url';
import { useSocialActions } from '@/hooks/use-social-actions';
import { cn } from '@/lib/utils';
import { SharedData, User } from '@/types';
import { usePage } from '@inertiajs/react';
import {
    RiBlueskyFill,
    RiGithubFill,
    RiLinkedinBoxFill,
    RiTwitterXFill,
    RiYoutubeFill,
} from '@remixicon/react';
import { format } from 'date-fns';
import {
    ArrowLeftIcon,
    ArrowRightIcon,
    BanIcon,
    EllipsisVerticalIcon,
    Globe,
    GraduationCap,
    InfoIcon,
    ShieldCheckIcon,
    UserIcon,
    UserMinusIcon,
    UserPlusIcon,
} from 'lucide-react';
import * as React from 'react';
import { useState } from 'react';

function OnboardingLink({
    url,
    name,
    children,
}: {
    url: string | undefined;
    name: string;
    children: React.ReactNode;
}) {
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
function OnboardingLinks({
    links,
}: {
    links: { name: string; url: string | undefined; icon: React.ReactNode }[];
}) {
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
        <div className="gradient mt-8 flex w-full flex-col items-start gap-2 space-y-6 rounded-lg bg-card p-4">
            <small>Skills</small>
            {skills?.length == 0 && (
                <small className="text-xs text-gray-300 dark:text-muted">
                    nenhuma skill definida
                </small>
            )}
            <div className="-mt-4 flex flex-wrap items-center gap-1 overflow-x-auto [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                {skills && <HighlightedSkills techs={skills} />}
            </div>
        </div>
    );
}

function OnboardingAbout({ about }: { about: string | undefined }) {
    return (
        <div className="gradient flex w-full flex-col items-start gap-2 space-y-6 rounded-lg bg-card p-4">
            <small>Sobre</small>
            {!about && (
                <small className="-mt-6 text-xs text-gray-300 dark:text-muted">
                    nenhuma informação disponivel
                </small>
            )}
            {about && <div className="-mt-4">{about}</div>}
        </div>
    );
}

interface SocialDropdownMenuProps {
    user: User;
    hasFollowed?: boolean;
}

export function SocialDropdownMenu({
    user,
    hasFollowed,
}: SocialDropdownMenuProps) {
    const { auth } = usePage<SharedData>().props;
    const { showProfile } = useProfile(user);
    const {
        processing,
        handleBlock,
        handleFollow,
        handleUnfollow,
        handleUnblock,
        state,
        setState,
    } = useSocialActions(user);
    const [step, setStep] = useState(1);
    const [open, setOpen] = useState(false);
    const totalSteps = 4;

    const links = [
        { name: 'GitHub', url: user.github_url, icon: <RiGithubFill /> },
        { name: 'Twitter', url: user.twitter_url, icon: <RiTwitterXFill /> },
        { name: 'YouTube', url: user.youtube_url, icon: <RiYoutubeFill /> },
        { name: 'Bluesky', url: user.bluesky_url, icon: <RiBlueskyFill /> },
        { name: 'Website', url: user.website_url, icon: <Globe /> },
        {
            name: 'LinkedIn',
            url: user.linkedin_url,
            icon: <RiLinkedinBoxFill />,
        },
    ];

    const handleContinue = () => {
        if (step < totalSteps) setStep(step + 1);
    };

    const handleBack = () => {
        if (step > 1) setStep(step - 1);
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

    const openOnboardingCard = () => {
        setStep(1);
        setOpen(true);
    };

    // Use hasFollowed prop if provided, otherwise fallback to user.has_followed
    const has_followed =
        hasFollowed !== undefined ? hasFollowed : user.has_followed;
    const isBlocked = user.is_blocked ?? false;

    return (
        <>
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
                    <DropdownMenuItem onClick={openOnboardingCard}>
                        <InfoIcon className="mr-2 size-4" />
                        Onboarding
                    </DropdownMenuItem>
                    <DropdownMenuItem onClick={showProfile}>
                        <UserIcon className="mr-2 size-4" />
                        Ver Perfil
                    </DropdownMenuItem>
                    {auth.user && auth.user.id !== user.id && (
                        <>
                            <DropdownMenuSeparator />
                            {!has_followed && (
                                <DropdownMenuItem
                                    onClick={handleFollow}
                                    disabled={processing}
                                >
                                    <UserPlusIcon className="mr-2 size-4" />
                                    Seguir
                                </DropdownMenuItem>
                            )}
                            {has_followed && (
                                <DropdownMenuItem
                                    onClick={handleUnfollow}
                                    disabled={processing}
                                >
                                    <UserMinusIcon className="mr-2 size-4" />
                                    Deixar de Seguir
                                </DropdownMenuItem>
                            )}
                            <DropdownMenuSeparator />
                            {isBlocked ? (
                                <DropdownMenuItem
                                    onClick={handleUnblock}
                                    className="text-green-600 dark:text-green-400"
                                >
                                    <ShieldCheckIcon className="mr-2 size-4" />
                                    Desbloquear
                                </DropdownMenuItem>
                            ) : (
                                <DropdownMenuItem
                                    onClick={() =>
                                        setState({
                                            ...state,
                                            blockDialogOpen: true,
                                        })
                                    }
                                    className="text-red-600 dark:text-red-400"
                                >
                                    <BanIcon className="mr-2 size-4" />
                                    Bloquear
                                </DropdownMenuItem>
                            )}
                        </>
                    )}
                </DropdownMenuContent>
            </DropdownMenu>
            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent className="w-ful overflow-auto">
                    <DialogHeader className="gradient sticky mb-0 w-full items-center justify-between rounded-lg bg-card px-4 pb-2 shadow-lg">
                        <div className="flex w-full items-start justify-start pt-4">
                            <OnboardingAvatar avatarUrl={user.avatar_url} />
                            <div className="ml-2 flex flex-col gap-1 text-left">
                                <div>
                                    <DialogTitle className="text-xl">
                                        {user.name}
                                    </DialogTitle>
                                    <small className="text-xs">
                                        {user.email}
                                    </small>
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
                                    {user.professional_educations?.map(
                                        (education) => (
                                            <Card
                                                className="w-full items-start p-4"
                                                key={education.id}
                                            >
                                                <CardTitle className="flex w-full items-center gap-1 text-sm font-semibold">
                                                    <GraduationCap />{' '}
                                                    {education.degree}
                                                </CardTitle>
                                                <CardContent className="-mt-2 grid w-full grid-cols-1 items-center gap-4 border-t-1 pt-4 md:grid-cols-2">
                                                    <div className="flex flex-col items-start justify-start">
                                                        <small className="text-xs text-gray-500">
                                                            Instituíção
                                                        </small>
                                                        <span className="bold text-sm">
                                                            {
                                                                education.institution
                                                            }
                                                        </span>
                                                    </div>
                                                    <div className="flex flex-col items-start justify-start">
                                                        <small className="text-xs text-gray-500">
                                                            Area de Estudo
                                                        </small>
                                                        <span className="bold text-sm">
                                                            {
                                                                education.field_of_study
                                                            }
                                                        </span>
                                                    </div>
                                                    <div className="flex flex-col items-start justify-start">
                                                        <small className="text-xs text-gray-500">
                                                            Inicio
                                                        </small>
                                                        <span className="bold text-sm">
                                                            {format(
                                                                new Date(
                                                                    education.start_date,
                                                                ),
                                                                'dd-MM-yyyy',
                                                            )}
                                                        </span>
                                                    </div>
                                                    {education.end_date && (
                                                        <div className="flex flex-col items-start justify-start">
                                                            <small className="text-xs text-gray-500">
                                                                Fim
                                                            </small>
                                                            <span className="bold text-sm">
                                                                {format(
                                                                    new Date(
                                                                        education.end_date,
                                                                    ),
                                                                    'dd-MM-yyyy',
                                                                )}
                                                            </span>
                                                        </div>
                                                    )}
                                                </CardContent>
                                            </Card>
                                        ),
                                    )}
                                </DialogDescription>
                            </div>
                        )}
                        {step === 3 && (
                            <>
                                <DialogTitle>
                                    Experiência Profissional
                                </DialogTitle>
                                <DialogDescription>
                                    Begin building amazing interfaces with our
                                    comprehensive component library.
                                </DialogDescription>
                            </>
                        )}
                        {step === 4 && (
                            <>
                                <DialogTitle>Projetos</DialogTitle>
                                <DialogDescription>
                                    Access our extensive documentation and
                                    community resources to make the most of
                                    Origin UI.
                                </DialogDescription>
                            </>
                        )}
                        <DialogFooter className="fixed right-0 bottom-0 left-0 z-10 flex w-full items-center justify-between border-t p-3">
                            <div className="max:order-1 flex justify-center space-x-1.5">
                                {[1, 2, 3, 4].map((s) => (
                                    <div
                                        key={s}
                                        className={cn(
                                            'size-1.5 rounded-full bg-primary',
                                            step === s
                                                ? 'bg-primary'
                                                : 'opacity-20',
                                        )}
                                    />
                                ))}
                            </div>
                            <div className="inline-flex gap-2">
                                {step > 1 && (
                                    <Button
                                        className="group"
                                        type="button"
                                        onClick={handleBack}
                                        variant="ghost"
                                    >
                                        <ArrowLeftIcon size={16} />
                                        Voltar
                                    </Button>
                                )}
                                {step < totalSteps ? (
                                    <Button
                                        className="group"
                                        type="button"
                                        onClick={handleContinue}
                                    >
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

            <BlockConfirmationDialog
                state={state}
                onOpenChange={(open) =>
                    setState((state) => ({ ...state, blockDialogOpen: open }))
                }
                name={user.name}
                disabled={processing}
                onClick={handleBlock}
            />

            <UnfollowConfirmationDialog
                state={state}
                onOpenChange={(open) =>
                    setState((state) => ({
                        ...state,
                        unfollowDialogOpen: open,
                    }))
                }
                name={user.name}
                disabled={processing}
                onClick={handleUnfollow}
            />

            <UnblockConfirmationDialog
                state={state}
                onOpenChange={(open) =>
                    setState((state) => ({
                        ...state,
                        unblockDialogOpen: open,
                    }))
                }
                name={user.name}
                disabled={processing}
                onClick={handleUnblock}
            />
        </>
    );
}
