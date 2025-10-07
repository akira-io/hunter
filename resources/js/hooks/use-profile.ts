import PublicProfileController from '@/actions/App/Http/Controllers/PublicProfileController';
import { useToast } from '@/hooks/use-toast';
import { User } from '@/types';
import { router } from '@inertiajs/react';

export const useProfile = (user: User) => {
    const { toast } = useToast();
    function show() {
        router.get(
            PublicProfileController.show({ user: user.id }).url,
            {
                preserveScroll: true,
                preserveState: true,
            },
            {
                onError: (error) => {
                    return toast({
                        variant: 'destructive',
                        description: error.message,
                    });
                },
            },
        );
    }

    return {
        showProfile: show,
    };
};
