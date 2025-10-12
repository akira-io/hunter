import { HunterConfirmationDialog } from '@/components/core/HunterConfirmationDialog';
import InputError from '@/components/input-error';
import { ProfileCard } from '@/components/profile-card';
import { Button } from '@/components/ui/button';
import { CharacterCounter } from '@/components/ui/character-counter';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { CONTENT_LIMITS } from '@/constants/validation';
import { useCharacterCount } from '@/hooks/use-character-count';
import { useToast } from '@/hooks/use-toast';
import { useUnsavedChangesGuard } from '@/hooks/use-unsaved-changes-guard';
import profile from '@/routes/profile';
import { useAboutStore } from '@/stores/about';
import type { SharedData } from '@/types';
import { useForm, usePage } from '@inertiajs/react';
import { EditIcon, PlusIcon, UserIcon } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

interface AboutForm {
    bio?: string;
}

export function About() {
    const { auth } = usePage<SharedData>().props;
    const { toast } = useToast();
    const { isOpen, open, close, set } = useAboutStore();
    const [showUnsavedDialog, setShowUnsavedDialog] = useState(false);

    const { data, setData, patch, errors, processing } = useForm<
        Required<AboutForm>
    >({
        bio: auth.user.bio ?? '',
    });

    const characterCount = useCharacterCount({
        content: data.bio,
        maxLength: CONTENT_LIMITS.BIO,
        trim: false,
    });

    const initialData = { bio: auth.user.bio ?? '' };
    const isDirty = data.bio !== initialData.bio;

    // Guard for Inertia navigation
    useUnsavedChangesGuard(isDirty && isOpen, {
        enabled: !processing,
        message:
            'Você tem alterações não salvas na apresentação. Se sair agora, essas alterações serão perdidas.',
    });

    const handleDialogChange = (open: boolean) => {
        // If trying to close the dialog
        if (!open) {
            // If there are unsaved changes, show confirmation
            if (isDirty && !processing) {
                setShowUnsavedDialog(true);
                return;
            }
            // No unsaved changes, allow closing
            close();
        } else {
            // Opening the dialog
            set(open);
        }
    };

    const handleConfirmClose = () => {
        setShowUnsavedDialog(false);
        // Reset form data to initial state
        setData('bio', initialData.bio);
        close();
    };

    const handleCancelClose = () => {
        setShowUnsavedDialog(false);
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        patch(profile.about().url, {
            preserveScroll: true,
            onFinish: () => {
                close();
                toast({
                    description:
                        'A sua apresentação foi atualizada com sucesso.',
                });
            },
        });
    };
    return (
        <ProfileCard
            title="Apresentação"
            icon={auth?.user.bio ? <EditIcon /> : <PlusIcon />}
            onClick={open}
        >
            {!auth.user.bio && (
                <>
                    <UserIcon />
                    Compartilhe um pouco sobre você e suas experiências. Isso
                    ajudará os recrutadores a conhecerem melhor o seu perfil.
                </>
            )}
            <p className="block w-full break-all whitespace-normal dark:text-gray-400">
                {auth.user.bio}
            </p>
            <Dialog open={isOpen} onOpenChange={handleDialogChange}>
                <DialogTrigger asChild>
                    <Button>
                        <UserIcon />
                        {auth.user.bio
                            ? 'Atualizar Apresentação'
                            : 'Adicionar Apresentação'}
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle className="text-zinc-900 dark:text-zinc-100">
                            Apresentação
                        </DialogTitle>
                        <DialogDescription className="text-zinc-600 dark:text-zinc-400">
                            Adicione uma breve descrição sobre você. Isso
                            ajudará os recrutadores a conhecerem melhor o seu
                            perfil.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submit}>
                        <Textarea
                            id="bio"
                            value={data.bio}
                            onChange={(e) => setData('bio', e.target.value)}
                            maxLength={CONTENT_LIMITS.BIO}
                            className="h-50"
                        />
                        <InputError className="mt-2" message={errors.bio} />
                        <div className="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                            <div className="flex-1">
                                <CharacterCounter
                                    count={characterCount.count}
                                    max={characterCount.max}
                                    isNearLimit={characterCount.isNearLimit}
                                    isOverLimit={characterCount.isOverLimit}
                                    className="justify-end sm:justify-start"
                                />
                            </div>
                            <Button
                                type="submit"
                                disabled={
                                    processing || !characterCount.canSubmit
                                }
                            >
                                <UserIcon /> Guardar
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            <HunterConfirmationDialog
                open={showUnsavedDialog}
                onOpenChange={setShowUnsavedDialog}
                onConfirm={handleConfirmClose}
                onCancel={handleCancelClose}
                title="Alterações não salvas"
                message="Você tem alterações por guardar na apresentação. Se sair agora, essas alterações serão perdidas."
                confirmText="Sair sem guardar"
                cancelText="Continuar editando"
            />
        </ProfileCard>
    );
}
