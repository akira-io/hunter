import { useToast } from '@/hooks/use-toast';
import profile from '@/routes/profile';
import { SharedData } from '@/types';
import { useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { genConfig } from 'react-nice-avatar';

type ProfileForm = {
    name: string;
    email: string;
    bio: string;
    user_name?: string;
    location?: string;
    avatar_url?: File | string | null;
    background_image_url: File | string | null;
};

export function useProfileUpdate() {
    const { toast } = useToast();
    const config = genConfig({ sex: 'man', hairStyle: 'thick' });
    const { auth } = usePage<SharedData>().props;

    const [open, setOpen] = useState(false);
    const [fileTooLargeDialogOpen, setFileTooLargeDialogOpen] = useState(false);
    const [fileTooLarge, setFileTooLarge] = useState(false);

    const [avatarPreview, setAvatarPreview] = useState<string | null>(
        auth.user.avatar_url || null,
    );
    const [backgroundPreview, setBackgroundPreview] = useState<string>(
        auth.user.background_image_url || '',
    );

    const { data, setData, post, errors, processing } = useForm<
        Required<ProfileForm>
    >({
        name: auth.user.name ?? '',
        email: auth.user.email ?? '',
        bio: auth.user.bio ?? '',
        user_name: auth.user.user_name ?? '',
        location: auth.user.location ?? '',
        avatar_url: auth.user.avatar_url ?? '',
        background_image_url: auth.user.background_image_url ?? '',
    });

    function handleFileChange(
        e: React.ChangeEvent<HTMLInputElement>,
        type: 'avatar' | 'background',
    ) {
        const file = e.target.files?.[0];
        if (file) {
            // Validate file size
            if (file.size > 2048 * 1024) {
                setFileTooLarge(true);
                setFileTooLargeDialogOpen(true);
                return;
            }

            // Validate file type
            const validImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!validImageTypes.includes(file.type)) {
                toast({
                    variant: 'destructive',
                    title: 'Invalid File Type',
                    description:
                        'Please upload a valid image file (JPEG, PNG, or GIF).',
                });
                return;
            }

            setFileTooLarge(false);

            // Generate and set preview URL
            const url = URL.createObjectURL(file);
            if (type === 'avatar') {
                setAvatarPreview(url);
                setData('avatar_url', file);
            } else {
                setBackgroundPreview(url);
                setData('background_image_url', file);
            }
        }
    }

    function removeImage(type: 'avatar' | 'background') {
        if (type === 'avatar') {
            setAvatarPreview(null);
            setData('avatar_url', null);
        } else {
            setBackgroundPreview('');
            setData('background_image_url', null);
        }
    }

    function updateProfile(e: React.FormEvent) {
        e.preventDefault();

        post(profile.update.url(), {
            preserveState: true,
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                setOpen(false);
                toast({
                    description: 'Perfil atualizado com sucesso.',
                });
            },
            onError: (errors) => {
                console.error('Errors:', errors);
                toast({
                    variant: 'destructive',
                    title: 'Erro ao atualizar o perfil',
                    description:
                        'Ocorreu um erro ao atualizar o perfil. Tente novamente mais tarde.',
                });
            },
        });
    }

    return {
        open,
        setOpen,
        fileTooLargeDialogOpen,
        setFileTooLargeDialogOpen,
        fileTooLarge,
        avatarPreview,
        backgroundPreview,
        data,
        setData,
        errors,
        processing,
        config,
        handleFileChange,
        removeImage,
        updateProfile,
    };
}
