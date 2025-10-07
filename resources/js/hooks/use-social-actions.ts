import FollowController from '@/actions/App/Http/Controllers/Followable/FollowController';
import UnFollowController from '@/actions/App/Http/Controllers/Followable/UnFollowController';
import PrivacyController from '@/actions/App/Http/Controllers/Settings/PrivacyController';
import { useToast } from '@/hooks/use-toast';
import { User } from '@/types';
import { useForm } from '@inertiajs/react';
import { useState } from 'react';

export const useSocialActions = (user: User) => {
    const { toast } = useToast();

    const [state, setState] = useState({
        unfollowDialogOpen: false,
        blockDialogOpen: false,
        unblockDialogOpen: false,
    });

    const {
        post,
        processing,
        delete: destroy,
    } = useForm({
        user_id: user.id,
    });

    function handleFollow() {
        post(FollowController.post().url, {
            preserveScroll: true,
            onSuccess: () => {
                return toast({
                    description: `Você começou a seguir ${user.name}`,
                });
            },
            onError: (error) => {
                toast({
                    variant: 'destructive',
                    description: error.message,
                });
            },
        });
    }

    function handleUnfollow() {
        post(UnFollowController.post().url, {
            preserveScroll: true,
            onSuccess: () => {
                toast({
                    description: `Você deixou de seguir ${user.name}`,
                });
                setState({
                    ...state,
                    unfollowDialogOpen: false,
                });
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
        post(PrivacyController.blockUser(user.id).url, {
            preserveScroll: true,
            onSuccess: () => {
                toast({
                    description: `${user.name} foi bloqueado com sucesso.`,
                });
                setState({
                    ...state,
                    blockDialogOpen: false,
                });
            },
            onError: () => {
                toast({
                    variant: 'destructive',
                    description: `Erro ao bloquear ${user.name}. Tente novamente.`,
                });
            },
        });
    }

    function handleUnblock() {
        destroy(PrivacyController.unblockUser(user.id).url, {
            preserveScroll: true,
            onSuccess: () => {
                toast({
                    description: `${user.name} foi desbloqueado com sucesso.`,
                });
                setState({
                    ...state,
                    unblockDialogOpen: false,
                });
            },
            onError: () => {
                toast({
                    variant: 'destructive',
                    description: `Erro ao desbloquear ${user.name}. Tente novamente.`,
                });
            },
        });
    }

    return {
        handleFollow,
        handleUnfollow,
        handleBlock,
        handleUnblock,
        processing,
        state,
        setState,
    };
};
