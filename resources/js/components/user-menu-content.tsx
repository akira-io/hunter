import { DropdownMenuItem } from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { logout } from '@/routes';
import hunts from '@/routes/hunts';
import profile from '@/routes/profile';
import settings from '@/routes/settings';
import { type User } from '@/types';
import { Link, useForm } from '@inertiajs/react';
import { BookOpen, Loader2, LogOut, Settings, Sparkles, Target, UserIcon } from 'lucide-react';

import OnboardingController from '@/actions/App/Http/Controllers/OnboardingController';
import { useToast } from '@/hooks/use-toast';

interface UserMenuContentProps {
    user: User;
}

export function UserMenuContent({ user }: UserMenuContentProps) {
    const cleanup = useMobileNavigation();
    const { post, processing } = useForm({});
    const { toast } = useToast();

    const handleReplayTutorial = () => {
        post(OnboardingController.destroy().url, {
            preserveScroll: true,
            onSuccess: () => {
                cleanup();
                toast({
                    title: 'Tutorial reiniciado',
                    description: 'O tutorial será aberto em alguns instantes...',
                    duration: 3000,
                });
            },
            onError: () => {
                toast({
                    title: 'Erro',
                    description: 'Não foi possível reiniciar o tutorial. Tente novamente.',
                    variant: 'destructive',
                    duration: 3000,
                });
            },
        });
    };

    return (
        <div className="divide-y divide-zinc-200 dark:divide-zinc-700">
            {/* User Info Section */}
            <div className="px-3 py-3 sm:px-4 sm:py-3">
                <div className="flex items-center gap-2 text-left text-sm">
                    <UserInfo user={user} />
                    <div className="grid flex-1 text-left text-sm leading-tight">
                        <span className="truncate font-semibold text-zinc-900 dark:text-zinc-100">{user.name}</span>
                        <span className="truncate text-xs text-zinc-500 dark:text-zinc-400">{user.email}</span>
                    </div>
                </div>
            </div>
            {/* Account Section */}
            <div className="py-1">
                <DropdownMenuItem asChild>
                    <Link className="block w-full px-3 py-2 sm:px-4 sm:py-2" href={hunts.index()} as="button" prefetch onClick={cleanup}>
                        <Sparkles className="mr-2 size-4 text-zinc-500 dark:text-zinc-400" />
                        <span className="text-zinc-900 dark:text-zinc-100">Hunts</span>
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <Link className="block w-full px-3 py-2 sm:px-4 sm:py-2" href={hunts.my()} as="button" prefetch onClick={cleanup}>
                        <Target className="mr-2 size-4 text-zinc-500 dark:text-zinc-400" />
                        <span className="text-zinc-900 dark:text-zinc-100">My Hunts</span>
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <Link className="block w-full px-3 py-2 sm:px-4 sm:py-2" href={profile.edit()} as="button" prefetch onClick={cleanup}>
                        <UserIcon className="mr-2 size-4 text-zinc-500 dark:text-zinc-400" />
                        <span className="text-zinc-900 dark:text-zinc-100">Perfil</span>
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <Link className="block w-full px-3 py-2 sm:px-4 sm:py-2" href={settings.index()} as="button" prefetch onClick={cleanup}>
                        <Settings className="mr-2 size-4 text-zinc-500 dark:text-zinc-400" />
                        <span className="text-zinc-900 dark:text-zinc-100">Definições</span>
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <button
                        className="block w-full px-3 py-2 text-left transition-opacity disabled:cursor-not-allowed disabled:opacity-50 sm:px-4 sm:py-2"
                        onClick={handleReplayTutorial}
                        disabled={processing}
                    >
                        {processing ? (
                            <Loader2 className="mr-2 size-4 animate-spin text-zinc-500 dark:text-zinc-400" />
                        ) : (
                            <BookOpen className="mr-2 size-4 text-zinc-500 dark:text-zinc-400" />
                        )}
                        <span className="text-zinc-900 dark:text-zinc-100">{processing ? 'A reiniciar tutorial...' : 'Repetir Tutorial'}</span>
                    </button>
                </DropdownMenuItem>
            </div>
            {/* Logout Section */}
            <div className="py-1">
                <DropdownMenuItem asChild>
                    <Link
                        className="block w-full px-3 py-2 text-red-600 sm:px-4 sm:py-2 dark:text-red-400"
                        method="post"
                        href={logout.post()}
                        as="button"
                        onClick={cleanup}
                    >
                        <LogOut className="mr-2 size-4" />
                        <span>Sair</span>
                    </Link>
                </DropdownMenuItem>
            </div>
        </div>
    );
}
